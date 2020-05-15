import { Request, Response } from 'restify';
import { Get, Post } from './Framework/Rest/rest-decorators';
import { ConnectionRegistry } from './ConnectionRegistry';
import { SessionIdProvider } from './SessionIdProvider';

export class RestController {
    private readonly sessionIdProvider = new SessionIdProvider();
    private readonly connectionRegistry: ConnectionRegistry;

    constructor (connectionRegistry: ConnectionRegistry) {
        this.connectionRegistry = connectionRegistry;
    }

    @Get('/stats')
    stats (request: Request, response: Response): void {
        response.send({
            connections: this.connectionRegistry.numConnections,
            registrations: this.connectionRegistry.numRegistrations,
            sessions: this.connectionRegistry.numRegisteredSessions
        });
    }

    @Get('/users/:id/is-online')
    async userIsConnected (request: Request, response: Response): Promise<any> {
        const userId = request.params.id;
        const sessionIds = await this.sessionIdProvider.fetchSessionIdsForUser(userId);
        const connections = this.connectionRegistry.getConnectionsForSessions(sessionIds);

        for (const connection of connections) {
            if (connection.clientIsHidden) {
                continue;
            }
            return response.send(true); // there at least one connection to a client that is visible to the user
        }

        return response.sendRaw('false', { 'Content-Type': 'application/json' }); // due to a bug in Restify, a normal send would result in false being casted to null
    }

    /**
     * :ids: You can post to multiple user ids separating them with commas (,).
     */
    @Post('/users/:ids/:channel/:method')
    async send (request: Request, response: Response): Promise<any> {
        const userIds: number[] = request.params.ids.split(',').map(Number);
        const sessionIds = await this.sessionIdProvider.fetchSessionIdsForUsers(userIds);
        const connections = this.connectionRegistry.getConnectionsForSessions(sessionIds);

        for (const connection of connections) {
            connection.send(request.params.channel, { m: request.params.method, o: request.body });
        }

        return response.send();
    }
}
