import * as util from 'util';
import * as fs from 'fs';
import { Tedis } from 'tedis';
import path = require('path');

export class SessionIdProvider {
    private readonly redisClient = new Tedis({
        host: process.env.REDIS_HOST ?? '127.0.0.1',
        port: Number(process.env.REDIS_PORT) || 6379
    });

    /**
     * This script can be uploaded to Redis to retrieve all current session ids of a user.
     */
    private readonly sessionIdsScriptFilename = path.join(__dirname, '../', 'session-ids.lua');
    /**
     * Once uploaded to Redis, the script is identified by an SHA hash. This can be used to tell Redis to execute the
     * script.
     */
    private sessionIdsScriptSHA: string;

    async fetchSessionIdsForUser (userId: number): Promise<string[]> {
        const sha = await this.getSessionIdsScriptSHA();
        try {
            return await this.redisClient.command('EVALSHA', sha, 0, userId);
        } catch (err) {
            if (err.code !== 'NOSCRIPT') {
                throw err;
            }
            await this.uploadSessionIdsScriptToRedis();
            return await this.redisClient.command('EVALSHA', sha, 0, userId);
        }
    }

    async fetchSessionIdsForUsers (userIds: number[]): Promise<string[]> {
        const sessionIds: string[] = [];
        for (const userId of userIds) {
            const sessionIdsForUser = await this.fetchSessionIdsForUser(userId);
            sessionIds.push(...sessionIdsForUser);
        }
        return sessionIds;
    }

    private async getSessionIdsScriptSHA (): Promise<string> {
        if (!this.sessionIdsScriptSHA) {
            await this.uploadSessionIdsScriptToRedis();
        }

        return this.sessionIdsScriptSHA;
    }

    private async uploadSessionIdsScriptToRedis (): Promise<void> {
        const contents = await util.promisify(fs.readFile)(this.sessionIdsScriptFilename, 'utf8');
        this.sessionIdsScriptSHA = await this.redisClient.command('SCRIPT', 'LOAD', contents);
    }
}
