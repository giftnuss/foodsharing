/**
 * Socket.io Decorators
 *
 * The decorators in these file can be read by the SocketIOServerFacade. You can use them to bind controller methods
 * to socket.io events.
 */

import { OnConnectionMetadata, OnSocketEventMetadata } from './EventActionMetadata';
import { pushMetadata } from '../push-to-metadata-array';

/**
 * A method with this decorator will be called whenever a client connects to the socket.io server. An instance of the
 * socket connection will be injected.
 */
export const OnSocketConnection = (): any => {
    return (targetObject: Record<string, any>, controllerMethodName: string) => {
        const metadata: OnConnectionMetadata = {
            controllerMethodName
        };
        pushMetadata('on-socket-connection', metadata, targetObject);
    };
};

/**
 * A method with this decorator will be registered to listen to the specified socket event. The socket connection
 * emitting this event will be injected as first parameter, followed by all of the event's arguments.
 */
export const OnSocketEvent = (eventName: string): any => {
    return (targetObject: Record<string, any>, controllerMethodName: string) => {
        const metadata: OnSocketEventMetadata = {
            eventName,
            controllerMethodName
        };
        pushMetadata('on-socket-event', metadata, targetObject);
    };
};
