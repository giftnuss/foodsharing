import 'reflect-metadata';

/**
 * We use the reflect-metadata library to save metadata for classes using decorators/annotations.
 *
 * This is a helper to push metadata values to an array of metadata lying under one key.
 *
 * The target needs to be an instance of the class the metadata should be defined for.
 */
export function pushMetadata (metadataKey: any, metadataValue: any, target: Record<string, any>): void {
    if (!Reflect.hasMetadata(metadataKey, target.constructor)) {
        Reflect.defineMetadata(metadataKey, [], target.constructor);
    }

    const metadataArray = Reflect.getMetadata(metadataKey, target.constructor);
    metadataArray.push(metadataValue);
}
