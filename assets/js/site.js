
function toRadians(deg) {
    return deg * Math.PI / 180
}

function getColor(percent){
    var hue = (percent * 120).toString(10);
    return ['hsl(', hue, ',100%,50%)'].join('');
}

function makePie(canvas, percent, r) {
	var ctx = canvas.getContext('2d');

	var color = getColor(percent);

	var cx = canvas.width / 2;
	var cy = canvas.height / 2;

	ctx.fillStyle = color;
	ctx.beginPath();
	ctx.moveTo(cx, cy);
	ctx.arc(cx, cy, r, toRadians(-90), toRadians(360 * percent - 90));
	ctx.lineTo(cx,cy);
	ctx.closePath();
	ctx.fill();

	ctx.fillStyle = "rgba(255, 255, 255, 0.7)";
	ctx.fillRect(8, cy - 7, canvas.width - 16, 15);

	ctx.fillStyle = 'black';
	ctx.font = '10px monospace';
	ctx.textAlign = 'center';
	ctx.fillText(Math.floor(percent * 100) + '%', cx, cy + 5);
}

$(document).ready(function(){
	$('#threads .percent a').each((function() {
		var percent = $(this).data('percent');
		var canvas = document.createElement('canvas');
		canvas.width = 40;
		canvas.height = 40;

		$(this).empty().append(canvas);
		
		makePie(canvas, percent, 20);
	}));
});
