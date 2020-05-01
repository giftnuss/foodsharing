type HttpMethod = 'get' | 'post' | 'del' | 'put' | 'opts'; // although I don't like abbrev., being consistent with Restify makes our life easier here

export interface RouteMetadata {
    readonly requestMethod: HttpMethod
    readonly path: string
    readonly controllerMethodName: string
}
