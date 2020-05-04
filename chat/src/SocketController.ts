import { OnSocketConnection, OnSocketEvent } from './Framework/WebSocket/socket-decorators';
import { Socket } from 'socket.io';
import { ConnectionRegistry } from './ConnectionRegistry';
import { parse as parseCookie } from 'cookie';
import { Connection } from './Connection';

export class SocketController {
    private readonly connectionRegistry: ConnectionRegistry;

    constructor (socketRegistry: ConnectionRegistry) {
        this.connectionRegistry = socketRegistry;
    }

    @OnSocketConnection()
    onConnect (socket: Socket): void {
        this.connectionRegistry.numConnections++;
    }

    @OnSocketEvent('register')
    onRegister (socket: Socket): void {
        const sessionId = this.readSessionId(socket);
        this.connectionRegistry.register(sessionId, new Connection(socket));
    }

    @OnSocketEvent('disconnect')
    onDisconnect (socket: Socket): void {
        const sessionId = this.readSessionId(socket);
        this.connectionRegistry.removeRegistration(sessionId, socket.id);
        this.connectionRegistry.numConnections--;
    }

    @OnSocketEvent('blur')
    onClientBlur (socket: Socket): void {
        const sessionId = this.readSessionId(socket);
        const connection = this.connectionRegistry.getConnection(sessionId, socket.id);
        if (!connection) {
            return;
        }
        connection.clientIsHidden = true;
    }

    @OnSocketEvent('focus')
    onClientFocus (socket: Socket): void {
        const sessionId = this.readSessionId(socket);
        const connection = this.connectionRegistry.getConnection(sessionId, socket.id);
        if (!connection) {
            return;
        }
        connection.clientIsHidden = false;
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
