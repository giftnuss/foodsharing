import {Request, Response} from "restify";
import {Get, Post} from "./Framework/Rest/rest-decorators";
import {ConnectionRepository} from "./ConnectionRepository";
import {SessionIdProvider} from "./SessionIdProvider";

export class RestController {
    private sessionIdProvider = new SessionIdProvider()
    private connectionRepository: ConnectionRepository;

    constructor(connectionRepository: ConnectionRepository) {
        this.connectionRepository = connectionRepository;
    }

    @Get('/stats')
    stats(request: Request, response: Response): void {
        response.send({
            connections: this.connectionRepository.numConnections,
            registrations: this.connectionRepository.numRegistrations,
            sessions: Object.keys(this.connectionRepository.connectedClients).length
        });
    }

    @Get('/user/:id/is-online')
    async userIsConnected(request: Request, response: Response)
    {
        const userId = request.params.id;
        const sessionIds = await this.sessionIdProvider.fetchSessionIdsForUser(userId);

        for (const sessionId of sessionIds) {
            if (sessionId in this.connectionRepository.connectedClients) {
                return response.send(true) // there is at least one session for userId
            }
        }

        return response.send(false);
    }

    /**
     * :ids: You can post to multiple user ids separating them with dashes (-).
     */
    @Post('/user/:ids/:channel/:method')
    async send(request: Request, response: Response) {
        const userIds: string[] = request.params.ids.split('-');

        for (const id of userIds) {
            await this.sendToUser(parseInt(id), request.params.channel, request.params.method, request.body);
        }
        return response.send();
    }

    private async sendToUser(userId: number, channel: string, method: string, payload: string) {
        const sessionIds = await this.sessionIdProvider.fetchSessionIdsForUser(userId);
        if (!sessionIds) {
            return ;
        }
        for (const sessionId of sessionIds) {
            if (!this.connectionRepository.connectedClients[sessionId]) {
                continue;
            }
            for (const connection of this.connectionRepository.connectedClients[sessionId]) {
                connection.emit(channel, { m: method, o: payload });
            }
        }
    }
}
