import {RestController} from "./RestController";
import {RestifyServerFacade} from "./Framework/Rest/RestifyServerFacade";
import {ConnectionRepository} from "./ConnectionRepository";
import {SocketIOServerFacade} from "./Framework/WebSocket/SocketIOServerFacade";
import {SocketController} from "./SocketController";

const connectionRepository = new ConnectionRepository();

const restServer = new RestifyServerFacade();
restServer.loadControllerDecorators(new RestController(connectionRepository));
restServer.listen(1338);

const socketServer = new SocketIOServerFacade();
socketServer.loadControllerDecorators(new SocketController(connectionRepository));
socketServer.listen(1337);
