import {RestController} from "./RestController";
import {RestifyServerFacade} from "./Framework/Rest/RestifyServerFacade";
import {SocketRegistry} from "./SocketRegistry";
import {SocketIOServerFacade} from "./Framework/WebSocket/SocketIOServerFacade";
import {SocketController} from "./SocketController";

const socketRegistry = new SocketRegistry();

const restServer = new RestifyServerFacade();
restServer.loadControllerDecorators(new RestController(socketRegistry));
restServer.listen(1338);

const socketServer = new SocketIOServerFacade();
socketServer.loadControllerDecorators(new SocketController(socketRegistry));
socketServer.listen(1337);
