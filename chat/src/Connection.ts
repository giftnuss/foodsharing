import { Socket } from 'socket.io';

export class Connection {
    constructor (
        private readonly socket: Socket
    ) {}

    get id (): string {
        return this.socket.id;
    }

    /**
     * Whether the client the connection belongs to is in a background position, where the user can not see it and
     * therefore misses updates.
     */
    clientIsHidden = false;

    send (channel: string, payload: any): void {
        this.socket.emit(channel, payload);
    }
}
