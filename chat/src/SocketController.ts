import { OnSocketConnection, OnSocketEvent } from './Framework/WebSocket/socket-decorators';
import { Socket } from 'socket.io';
import { SocketRegistry } from './SocketRegistry';
import { parse as parseCookie } from 'cookie';

export class SocketController {
    private readonly socketRegistry: SocketRegistry;

    constructor (socketRegistry: SocketRegistry) {
        this.socketRegistry = socketRegistry;
    }

    @OnSocketConnection()
    onConnect (socket: Socket): void {
        this.socketRegistry.numConnections++;
    }

    @OnSocketEvent('register')
    onRegister (socket: Socket): void {
        const sessionId = this.readSessionId(socket);
        this.socketRegistry.register(sessionId, socket);
    }

    @OnSocketEvent('disconnect')
    onDisconnect (socket: Socket): void {
        const sessionId = this.readSessionId(socket);
        this.socketRegistry.removeRegistration(sessionId, socket);
        this.socketRegistry.numConnections--;
    }

    private readSessionId (socket: Socket): string {
        const cookieVal = socket.request.headers.cookie;
        if (!cookieVal) {
            throw new Error('not authorized');
        }
        const cookie = parseCookie(cookieVal);

        return cookie.PHPSESSID || cookie.sessionid;
    }
}
