import { Request, Response } from 'restify';
import { Get, Post } from './Framework/Rest/rest-decorators';
import { SocketRegistry } from './SocketRegistry';
import { SessionIdProvider } from './SessionIdProvider';

export class RestController {
    private readonly sessionIdProvider = new SessionIdProvider();
    private readonly socketRegistry: SocketRegistry;

    constructor (connectionRepository: SocketRegistry) {
        this.socketRegistry = connectionRepository;
    }

    @Get('/stats')
    stats (request: Request, response: Response): void {
        response.send({
            connections: this.socketRegistry.numConnections,
            registrations: this.socketRegistry.numRegistrations,
            sessions: this.socketRegistry.numRegisteredSessions
        });
    }

    @Get('/user/:id/is-online')
    async userIsConnected (request: Request, response: Response): Promise<any> {
        const userId = request.params.id;
        const sessionIds = await this.sessionIdProvider.fetchSessionIdsForUser(userId);

        for (const sessionId of sessionIds) {
            if (this.socketRegistry.hasSocketForSession(sessionId)) {
                return response.send(true); // there is at least one socket connection for userId
            }
        }

        return response.send(false);
    }

    /**
     * :ids: You can post to multiple user ids separating them with dashes (-).
     */
    @Post('/user/:ids/:channel/:method')
    async send (request: Request, response: Response): Promise<any> {
        const userIds: number[] = request.params.ids.split('-').map(Number);
        const sessionIds = await this.sessionIdProvider.fetchSessionIdsForUsers(userIds);
        const sockets = this.socketRegistry.getSocketsForSessions(sessionIds);

        for (const socket of sockets) {
            socket.emit(request.params.channel, { m: request.params.method, o: request.body });
        }

        return response.send();
    }
}
