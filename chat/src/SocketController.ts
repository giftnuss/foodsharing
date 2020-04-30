import {OnSocketConnection, OnSocketEvent} from "./Framework/WebSocket/socket-decorators";
import {Socket} from "socket.io";
import {ConnectionRepository} from "./ConnectionRepository";
import {parse as parseCookie} from "cookie";

export class SocketController {
    private connectionRepository: ConnectionRepository;

    constructor(connectionRepository: ConnectionRepository) {
        this.connectionRepository = connectionRepository;
    }

    @OnSocketConnection()
    onConnect(socket: Socket) {
        this.connectionRepository.numConnections++;
    }

    @OnSocketEvent('register')
    onRegister(socket: Socket) {
        const sessionId = this.readSessionId(socket);
        this.connectionRepository.numRegistrations++
        if (!this.connectionRepository.connectedClients[sessionId]) this.connectionRepository.connectedClients[sessionId] = []
        this.connectionRepository.connectedClients[sessionId].push(socket)
    }

    @OnSocketEvent('disconnect')
    onDisconnect(socket: Socket) {
        const sessionId = this.readSessionId(socket);
        this.connectionRepository.numConnections--
        const connections = this.connectionRepository.connectedClients[sessionId]
        if (sessionId && connections) {
            if (connections.includes(socket)) {
                connections.splice(connections.indexOf(socket), 1)
                this.connectionRepository.numRegistrations--
            }
            if (connections.length === 0) {
                delete this.connectionRepository.connectedClients[sessionId]
            }
        }
    }

    private readSessionId(socket: Socket): string {
        const cookieVal = socket.request.headers.cookie;
        if (!cookieVal) {
            throw new Error('not authorized');
        }
        const cookie = parseCookie(cookieVal)

        return cookie.PHPSESSID || cookie.sessionid
    }
}
