import 'reflect-metadata';
import * as restify from 'restify';
import { plugins, Request, Response, Server } from 'restify';
import { RouteMetadata } from './RouteMetadata';
import { ServerFacade } from '../ServerFacade';
import bodyParser = plugins.bodyParser;

export class RestifyServerFacade implements ServerFacade {
    private readonly server: Server;

    constructor () {
        this.server = restify.createServer({ maxParamLength: 50000 });
        this.server.use(bodyParser({ mapParams: false }));
    }

    listen (port: number): void {
        this.server.listen(port);
    }

    /**
     * Reads out the route configuration decorators from the given controller and makes the routes accessible
     * over the server. Supports all decorators that provide the 'routes' metadata key with instances of RouteMetadata.
     *
     * You can find supported decorators in rest-decorators.ts
     */
    loadControllerDecorators (controller: Record<string, any> & any): void {
        const routes: RouteMetadata[] = Reflect.getMetadata('routes', controller.constructor); // use constructor to identify the class at runtime
        for (const route of routes) {
            const methodName: string = route.controllerMethodName;

            if (typeof controller[methodName] !== 'function') {
                throw new Error(`Method ${methodName} is not defined on the given controller.`);
            }

            this.server[route.requestMethod](route.path, (request: Request, response: Response) => {
                let result: any;

                try {
                    result = controller[methodName](request, response);
                } catch (error) {
                    response.send(500, error);
                    return;
                }

                if (result instanceof Promise) {
                    result.catch(error => response.send(500, error));
                }
            });
        }
    }
}
