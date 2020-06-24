export interface OnConnectionMetadata {
    readonly controllerMethodName: string
}

export interface OnSocketEventMetadata {
    readonly eventName: string
    readonly controllerMethodName: string
}
