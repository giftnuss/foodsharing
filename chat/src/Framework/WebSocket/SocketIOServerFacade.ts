import 'reflect-metadata';
import { ServerFacade } from '../ServerFacade';
import { createServer } from 'http';
import { OnConnectionMetadata, OnSocketEventMetadata } from './EventActionMetadata';
import * as SocketIO from 'socket.io';

export class SocketIOServerFacade implements ServerFacade {
    private readonly server = createServer();
    private readonly socketIo = SocketIO(this.server);

    listen (port: number): void {
        this.server.listen(port);
    }

    /**
     * Reads out the socket server configuration decorators from the given controller and registers the events on the
     * socket server.
     *
     * You can find supported decorators in socket-decorators.ts
     */
    loadControllerDecorators (controller: Record<string, any> & any): void {
    // register methods with OnSocketConnection() decorators to socketIo.on('connection')
        const connectionMetadata: OnConnectionMetadata[] = Reflect.getMetadata('on-socket-connection', controller.constructor); // use constructor to identify the class at runtime

        for (const metadata of connectionMetadata) {
            const methodName: string = metadata.controllerMethodName;

            if (typeof controller[methodName] !== 'function') {
                throw new Error(`Method ${methodName} is not defined on the given controller.`);
            }

            this.socketIo.on('connection', socket => {
                let result;
                try {
                    result = controller[methodName](socket);
                } catch (error) {
                    socket.error(error);
                }
                if (result instanceof Promise) {
                    result.catch(error => socket.error(error));
                }
            });
        }

        // register methods with OnSocketEvent() decorators to socket.on('event-name')
        const socketEventMetadata: OnSocketEventMetadata[] = Reflect.getMetadata('on-socket-event', controller.constructor);
        for (const metadata of socketEventMetadata) {
            const methodName: string = metadata.controllerMethodName;

            if (typeof controller[methodName] !== 'function') {
                throw new Error(`Method ${methodName} is not defined on the given controller.`);
            }

            this.socketIo.on('connection', (socket) => {
                socket.on(metadata.eventName, (...args) => {
                    let result;
                    try {
                        result = controller[methodName](socket, ...args);
                    } catch (error) {
                        socket.error(error);
                    }
                    if (result instanceof Promise) {
                        result.catch(error => socket.error(error));
                    }
                });
            }
            );
        }
    }
}
