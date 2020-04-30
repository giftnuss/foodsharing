import * as util from "util";
import * as fs from "fs";
import {Tedis} from "tedis";

export class SessionIdProvider {
    private redisClient = new Tedis({
        host: process.env.REDIS_HOST || '127.0.0.1',
        port: Number(process.env.REDIS_PORT) || 6379
    });
    /**
     * This script can be uploaded to Redis to retrieve all current session ids of a user.
     */
    private sessionIdsScriptFilename = `${__dirname}/../session-ids.lua`;
    /**
     * Once uploaded to Redis, the script is identified by an SHA hash. This can be used to tell Redis to execute the
     * script.
     */
    private sessionIdsScriptSHA: string;

    async fetchSessionIdsForUser(userId: number): Promise<string[]> {
        const sha = await this.getSessionIdsScriptSHA();
        try {
            return await this.redisClient.command('EVALSHA', sha, 0, userId); // return value due to the lua script session-ids.lua
        } catch (err) {
            if (err.code !== 'NOSCRIPT') {
                throw err;
            }
            await this.uploadSessionIdsScriptToRedis();
            return this.fetchSessionIdsForUser(userId); // BEWARE OF ENDLESS LOOPS!
        }
    }

    private async getSessionIdsScriptSHA(): Promise<string> {
        if (!this.sessionIdsScriptSHA) {
            await this.uploadSessionIdsScriptToRedis();
        }

        return this.sessionIdsScriptSHA;
    }

    private async uploadSessionIdsScriptToRedis(): Promise<void> {
        const contents = await util.promisify(fs.readFile)(this.sessionIdsScriptFilename, 'utf8');
        this.sessionIdsScriptSHA = await this.redisClient.command('SCRIPT', 'LOAD', contents);
    }
}
