import { Socket } from 'socket.io';

export class SocketRegistry {
    private readonly registeredSockets: Map<string, Socket[]> = new Map<string, Socket[]>();
    private _numRegistrations = 0;

    /**
     * This is only used for /stats. Maybe this can be removed, because maybe nobody ever requests /stats.
     */
    numConnections = 0;

    register (sessionId: string, socket: Socket): void {
        let socketsForSessionId = this.registeredSockets.get(sessionId);
        if (!socketsForSessionId) {
            socketsForSessionId = [];
            this.registeredSockets.set(sessionId, socketsForSessionId);
        }
        socketsForSessionId.push(socket);
        this._numRegistrations++;
    }

    removeRegistration (sessionId: string, socket: Socket): void {
        const socketsForSession = this.registeredSockets.get(sessionId);

        if (!socketsForSession || !socketsForSession.includes(socket)) {
            return;
        }

        socketsForSession.splice(socketsForSession.indexOf(socket), 1);
        this._numRegistrations--;

        if (socketsForSession.length === 0) {
            this.registeredSockets.delete(sessionId);
        }
    }

    getSocketsForSessions (sessionIds: string[]): Socket[] {
        const sockets: Socket[] = [];
        for (const sessionId of sessionIds) {
            const socketsForSession = this.registeredSockets.get(sessionId);
            if (!socketsForSession) {
                continue;
            }
            sockets.push(...socketsForSession);
        }
        return sockets;
    }

    hasSocketForSession (sessionId: string): boolean {
        return this.registeredSockets.has(sessionId);
    }

    get numRegistrations (): number {
        return this._numRegistrations;
    }

    get numRegisteredSessions (): number {
        return this.registeredSockets.size;
    }
}
