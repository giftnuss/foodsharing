import {Socket} from "socket.io";

export class ConnectionRepository {
    connectedClients: {[key: string]: Socket[]} = {}
    numRegistrations = 0
    numConnections = 0
}
