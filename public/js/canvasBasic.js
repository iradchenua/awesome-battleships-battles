
function SpaceShip(params) {
    for(let prop in params) {
        if (params.hasOwnProperty(prop))
            this[prop] = params[prop];
    }
    this.width *= this.X_STEP;
    this.height *= this.Y_STEP;
    this.draw();
}

SpaceShip.prototype.draw = function() {
    this.ctx.fillStyle = this.color;
    this.ctx.fillRect(this.x, this.y,
                        this.width, this.height);
};

function drawGrid(canvas, ctx, X_STEP, Y_STEP) {
    for(let x = 0; x < canvas.width ; x += X_STEP)
        ctx.strokeRect(x, 0, X_STEP, canvas.height);
    for(let y = 0; y < canvas.width ; y += Y_STEP)
        ctx.strokeRect(0, y, canvas.width, Y_STEP);
}
$(document).ready(function() {

    const canvas = $('#cnv').get(0);
    const ctx = canvas.getContext('2d');
    const jsInfo = $('#jsInfo');

    const GAME_FIELD_WIDTH = jsInfo.data('gameFieldWidth');
    const GAME_FIELD_HEIGHT = jsInfo.data('gameFieldHeight');
    const X_STEP = canvas.width / GAME_FIELD_WIDTH;
    const Y_STEP = canvas.height / GAME_FIELD_HEIGHT;

    drawGrid(canvas, ctx, X_STEP, Y_STEP);
    const shipParams = {
        'width': 10,
        'height': 10,
        'x': 0,
        'y': 0,
        'ctx': ctx,
        'color': '#FF0000',
        'X_STEP': X_STEP,
        'Y_STEP': Y_STEP
    };
    let spaceShip = new SpaceShip(shipParams);
});