const PHASES=['order', 'movement', 'shoot'];

class EntityOnMap extends Phaser.GameObjects.Sprite {
    constructor(scene, x, y, props) {
        super(scene, x, y, props.img);

        this.id = props.id;

        this.displayWidth = props.width;
        this.displayHeight = props.height;

        this._shift();

        this.scene = scene;
        this.scene.add.existing(this);
    }
    destroy() {
        super.destroy();
    }
    _shift() {
        this.x += this.displayWidth / 2;
        this.y += this.displayHeight / 2;
    }
    update(props) {
        this.x = props.x;
        this.y = props.y;

        this._shift();
    }
}

class Obstacle extends EntityOnMap {
    constructor(scene, x, y, props) {
        super(scene, x, y, props);
    }
}

class SpaceShip extends EntityOnMap {
    constructor(scene, x, y, props) {
        super(scene, x, y, props);
        this.name = name;
        this._setDir(props);
    }
    update(props) {
        super.update(props);

        if (props.hullPoints <= 0) {
            this.destroy();
            return ;
        }
        this._setDir(props);
    }
    _setDir(props) {
        this.dirY = props.dirY;
        this.dirX = props.dirX;

        this.rotation = this._getAngle();
    }
    _getAngle() {
        if (this.dirX === 0) {
            return this.dirY === 1 ? -Math.PI / 2 : Math.PI / 2;
        }
        else {
            return this.dirX === 1 ? 0 : Math.PI;
        }
    }
}

class Game extends Phaser.Game {
    constructor(props) {
        super(props);
        this.width = props.width;
        this.height = props.height;

        this.gameFieldWidth = props.gameFieldWidth;
        this.gameFieldHeight = props.gameFieldHeight;

        this.xStep = this.width / this.gameFieldWidth;
        this.yStep = this.height / this.gameFieldHeight;

        this.shipsFromServer = props.shipsFromServer;
        this.obstaclesFromServer = props.obstaclesFromServer;
        this.notActivatedShip = props.notActivatedShip;
    }

    _xToCanvasCords(x) {
        return this.xStep * (x + this.gameFieldWidth / 2);
    }
    _yToCanvasCords(y) {
        return this.yStep * (-y + this.gameFieldHeight / 2);
    }
    _drawAllBounds() {
        this.graphics.clear();
        this.obstacles.forEach(obstacle => this._drawBounds(obstacle));
        this.ships.forEach(obstacle => this._drawBounds(obstacle));
    }
    _drawBounds(entityOnMap) {
        this.graphics.lineStyle(3, 0xffff37);
        this.graphics.strokeRectShape(entityOnMap.getBounds());
    }
    _stretchProps(props) {
        props.xCanvas = this._xToCanvasCords(props.x);
        props.yCanvas = this._yToCanvasCords(props.y);

        props.width *= this.xStep;
        props.height *= this.yStep;
    }
    createObstacles(scene) {
        this.obstacles = [];
        const obstaclesFromServer = this.obstaclesFromServer;

        obstaclesFromServer.forEach((obstacleProps) => {
            this._stretchProps(obstacleProps);

            this.obstacles[obstacleProps.id] = new Obstacle(scene,
                obstacleProps.xCanvas,
                obstacleProps.yCanvas,
                obstacleProps);

            this._drawBounds(this.obstacles[obstacleProps.id]);
        });
        return this.obstacles;
    }

    createShips(scene) {
        this.ships = [];
        const shipsFromServer = this.shipsFromServer;
        for (let userId in shipsFromServer) {
            if (shipsFromServer.hasOwnProperty(userId)) {
                shipsFromServer[userId].forEach((shipProps) => {
                    if (shipProps.hullPoints <= 0) {
                        return;
                    }
                    this._stretchProps(shipProps);

                    const ship = new SpaceShip(scene,
                        shipProps.xCanvas,
                        shipProps.yCanvas,
                        shipProps);

                    ship.setInteractive();

                    ship.on("clicked", this.onShipClick.bind(this), ship);

                    this._drawBounds(ship);
                    this.ships[shipProps.id] = ship;
                });
            }
        }
        return this.ships;
    }
    updateShips(shipsFromServer) {
        for(let userId in shipsFromServer) {
            if (shipsFromServer.hasOwnProperty(userId)) {
                shipsFromServer[userId].forEach((shipProps) => {
                    shipProps.x = this._xToCanvasCords(shipProps.x);
                    shipProps.y = this._yToCanvasCords(shipProps.y);

                    this.ships[shipProps.id].update(shipProps);
                });
            }
        }
        this._drawAllBounds();
    }
    onShipClick(ship) {
        if (PHASES[this.notActivatedShip.phase] !== 'shoot' ||
            this.notActivatedShip.id === ship.id) {
            return ;
        }

        this._toShootId = ship.id;
        console.log(this._toShootId);
    }
    expandDataToSend(form, buttonName) {
        if (buttonName === 'shoot[shoot]') {
            if (this._toShootId) {
                $("#shoot_toShoot").attr({"value": this._toShootId});
            }
        }
    }
}


function preload() {
    this.load.image('bg', 'images/space.jpg');
    this.load.image('red', 'images/red.png');
    this.load.image('blue', 'images/blue.png');
    this.load.image('island', 'images/island.png');
}

function create() {
    this.bg = this.add.image(0, 0, 'bg');
    this.graphics = this.add.graphics({ fillStyle: { color: 0x0000ff } });

    this.game.graphics = this.graphics;

    this.ships = this.game.createShips(this);
    this.obstacles = this.game.createObstacles(this);

    this.input.on('gameobjectup', function (pointer, gameObject)
    {
        gameObject.emit('clicked', gameObject);
    }, this);
}

function update() {

}

function phaseShow(notActivatedShip) {
    $('#no-free-ship').hide();
    for(let i = 0; i < PHASES.length; i++) {
        $('#phase' + i).hide();
    }
    if (Array.isArray(notActivatedShip))
    {
        $('#no-free-ship').show();
        return ;
    }
    $("#phase" + notActivatedShip.phase).show();
}
function dumpShip(notActivatedShip)
{
    let shipDiv = $('#ship');
    shipDiv.empty();
    shipDiv.hide();

    if (Array.isArray(notActivatedShip)) {
        return ;
    }
    shipDiv.append('<h3>' + notActivatedShip.name + '</h3>');
    shipDiv.append('<img src="images/' + notActivatedShip.img + '.png"' + '>');
    shipDiv.append('<p>phase: ' + PHASES[notActivatedShip.phase] + '</p>');
    for(let prop in notActivatedShip) {
        if (notActivatedShip.hasOwnProperty(prop) &&
            prop !== 'img' &&
            prop !== 'name' &&
            prop !=='phases')
        {
            shipDiv.append('<p>' + prop + ': ' + notActivatedShip[prop] + '</p>');
        }
    }
    shipDiv.show();
}
function updateFlash(message)
{
    $('#flash-success').text('');
    $('#flash-fail').text('');
    const jsonMessage = JSON.parse(message);
    for(let key in jsonMessage) {
        if (jsonMessage.hasOwnProperty(key)) {
            $('#flash-' + key   ).text(jsonMessage[key])
        }
    }

}
$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

$(document).ready(function() {
    let game;

    $.post("/init", function (data) {
        let config = {
            type: Phaser.CANVAS,
            parent: 'canvas-parent',
            width: data.width,
            height: data.height,
            gameFieldWidth: data.gameFieldWidth,
            gameFieldHeight: data.gameFieldHeight,
            obstaclesFromServer:  JSON.parse(data.obstacles),
            scene: {
                'preload': preload,
                'create': create,
                'update': update
            }
        };
        $.post("/ships", function(data) {
            config.shipsFromServer = JSON.parse(data.ships);
            config.notActivatedShip = JSON.parse(data.notActivatedShip);
            game = new Game(config);
            dumpShip(config.notActivatedShip);
            phaseShow(config.notActivatedShip);
        });
    });

    $('#form-container form button').click(function(e) {
        const button = $(this);
        let form = button.parent().parents('form').first();
        form.attr({'buttonName': button.attr('name')});
    });

    $('#form-container form').on('submit', function(e) {
        e.preventDefault();
        if (!game) {
            return false;
        }
        const form = $(this);
        const url = form.attr('action');
        const buttonName = form.attr('buttonName');
        game.expandDataToSend(form, buttonName);
        let data = form.serializeObject();
        data[buttonName] = '';

        $.post(
            {
                'url': url,
                'data': data
            }, function(response) {
                updateFlash(response.message);
                game.updateShips(JSON.parse(response.ships));
                response.notActivatedShip = JSON.parse(response.notActivatedShip);
                game.notActivatedShip = response.notActivatedShip;
                dumpShip(response.notActivatedShip);
                phaseShow(response.notActivatedShip);
            }, 'JSON').fail(function(response) {
            console.log('fuck you hacker!!!');
        });
    });
});