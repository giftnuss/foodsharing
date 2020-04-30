import {Request, Response} from "restify";
import {Get, Post} from "./Framework/Rest/rest-decorators";
import * as util from "util";
import * as fs from "fs";
import {Tedis} from "tedis";
import {ConnectionRepository} from "./ConnectionRepository";

export class RestController {
    private connectionRepository: ConnectionRepository;

    private sessionIdsScriptSHA: string;
    private sessionIdsScriptFilename = `${__dirname}/../session-ids.lua`;

    private redisClient = new Tedis({
        host: process.env.REDIS_HOST || '127.0.0.1',
        port: Number(process.env.REDIS_PORT) || 6379
    });

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
        const sessionIds = await this.fetchSessionIdsForUser(userId);

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
        const sessionIds = await this.fetchSessionIdsForUser(userId);
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

    private async fetchSessionIdsForUser(userId: number): Promise<string[]> {
        const sha = await this.getSessionIdsScriptSHA();
        try {
            return await this.redisClient.command('EVALSHA', sha, 0, userId); // return value due to the lua script session-ids.lua
        } catch (err) {
            if (err.code !== 'NOSCRIPT') {
                throw err;
            }
            await this.loadSessionIdsScript();
            return this.fetchSessionIdsForUser(userId); // BEWARE OF ENDLESS LOOPS!
        }
    }

    private async getSessionIdsScriptSHA(): Promise<string> {
        if (!this.sessionIdsScriptSHA) {
            await this.loadSessionIdsScript();
        }

        return this.sessionIdsScriptSHA;
    }

    private async loadSessionIdsScript(): Promise<void> {
        const contents = await util.promisify(fs.readFile)(this.sessionIdsScriptFilename, 'utf8');
        this.sessionIdsScriptSHA = await this.redisClient.command('SCRIPT', 'LOAD', contents);
    }
}
