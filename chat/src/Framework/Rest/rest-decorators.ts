/**
 * Rest Decorators
 *
 * The decorators in these file can be read by the RestServerFacade.
 *
 * These decorators can be used to declare routes to controller methods. Use the decorator matching your desired
 * request method and pass the path as a string.
 */

import { RouteMetadata } from './RouteMetadata';
import { pushMetadata } from '../push-to-metadata-array';

export const Get = (path: string): any => {
    return (targetObject: Record<string, any>, controllerMethodName: string) => {
        const route: RouteMetadata = {
            requestMethod: 'get',
            path,
            controllerMethodName
        };
        pushMetadata('routes', route, targetObject);
    };
};

export const Post = (path: string): any => {
    return (targetObject: Record<string, any>, controllerMethodName: string) => {
        const route: RouteMetadata = {
            requestMethod: 'post',
            path,
            controllerMethodName
        };
        pushMetadata('routes', route, targetObject);
    };
};
