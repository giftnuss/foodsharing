import { Connection } from './Connection';

export class ConnectionRegistry {
    private readonly registeredConnections: Map<string, Connection[]> = new Map<string, Connection[]>();
    private _numRegistrations = 0;

    /**
     * This is only used for /stats. Maybe this can be removed, because maybe nobody ever requests /stats.
     */
    numConnections = 0;

    register (sessionId: string, connection: Connection): void {
        let socketsForSessionId = this.registeredConnections.get(sessionId);
        if (!socketsForSessionId) {
            socketsForSessionId = [];
            this.registeredConnections.set(sessionId, socketsForSessionId);
        }
        socketsForSessionId.push(connection);
        this._numRegistrations++;
    }

    removeRegistration (sessionId: string, connectionId: string): void {
        const connectionsForSession = this.registeredConnections.get(sessionId);
        if (!connectionsForSession) {
            return;
        }
        const index = connectionsForSession.findIndex((connection) => connection.id === connectionId);

        if (index === -1) {
            return;
        }

        connectionsForSession.splice(index, 1);
        this._numRegistrations--;

        if (connectionsForSession.length === 0) {
            this.registeredConnections.delete(sessionId);
        }
    }

    getConnection (sessionId: string, connectionId: string): Connection | undefined {
        const connectionsForSession = this.registeredConnections.get(sessionId);
        if (!connectionsForSession) {
            return;
        }
        return connectionsForSession.find((connection) => connection.id === connectionId);
    }

    getConnectionsForSessions (sessionIds: string[]): Connection[] {
        const sockets: Connection[] = [];
        for (const sessionId of sessionIds) {
            const socketsForSession = this.registeredConnections.get(sessionId);
            if (!socketsForSession) {
                continue;
            }
            sockets.push(...socketsForSession);
        }
        return sockets;
    }

    get numRegistrations (): number {
        return this._numRegistrations;
    }

    get numRegisteredSessions (): number {
        return this.registeredConnections.size;
    }
}
