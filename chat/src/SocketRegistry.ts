import {Socket} from "socket.io";

export class SocketRegistry {
    private registeredSockets: {[sessionId: string]: Socket[]} = {};
    private _numRegistrations = 0;

    /**
     * This is only used for /stats. Maybe this can be removed, because maybe nobody ever requests /stats.
     */
    numConnections = 0;

    register(sessionId: string, socket: Socket): void {
        if (!this.registeredSockets[sessionId]) {
            this.registeredSockets[sessionId] = [];
        }
        this.registeredSockets[sessionId].push(socket);
        this._numRegistrations++;
    }

    removeRegistration(sessionId: string, socket: Socket): void {
        const socketsForSession = this.registeredSockets[sessionId];

        if (!socketsForSession || !socketsForSession.includes(socket)) {
            return;
        }

        socketsForSession.splice(socketsForSession.indexOf(socket), 1)
        this._numRegistrations--

        if (socketsForSession.length === 0) {
            delete this.registeredSockets[sessionId];
        }
    }

    getSocketsForSessions(sessionIds: string[]): Socket[] {
        const sockets: Socket[] = [];
        for (const sessionId of sessionIds) {
            sockets.push(...this.registeredSockets[sessionId])
        }
        return sockets;
    }

    hasSocketForSession(sessionId: string): boolean {
        return sessionId in this.registeredSockets;
    }

    get numRegistrations(): number {
        return this._numRegistrations;
    }

    get numRegisteredSessions(): number {
        return Object.keys(this.registeredSockets).length;
    }
}
