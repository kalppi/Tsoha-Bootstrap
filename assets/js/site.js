
function toRadians(deg) {
    return deg * Math.PI / 180
}

function getHue(percent){
    return (percent * 120).toString(10);
}

function makePie(canvas, percent, r, read) {
	var ctx = canvas.getContext('2d');

	var color = ['hsl(', getHue(percent), ',100%,50%)'].join('');

	var cx = canvas.width / 2;
	var cy = canvas.height / 2;
	var fontSize = Math.round(r / 2);

	ctx.fillStyle = color;
	ctx.beginPath();
	ctx.moveTo(cx, cy);
	ctx.arc(cx, cy, Math.min(r - 4, r * 0.85), toRadians(-90), toRadians(360 * percent - 90));
	ctx.lineTo(cx,cy);
	ctx.closePath();
	ctx.fill();

	ctx.fillStyle = "rgba(255, 255, 255, 0.7)";
	ctx.fillRect(8, cy - fontSize / 2.5, canvas.width - 16, fontSize);

	ctx.fillStyle = 'black';
	ctx.font =  fontSize + 'px monospace';
	ctx.textAlign = 'center';
	ctx.fillText(Math.floor(percent * 100) + '%', cx, cy + fontSize / 2);

	if(read) {
		ctx.beginPath();
		ctx.arc(cx, cy, r - 1, 0, 2 * Math.PI, false);
		ctx.lineWidth = 2;
		ctx.strokeStyle = ['hsl(', getHue(percent), ',100%,42%)'].join('');;
		ctx.stroke();
	}
}

$(document).ready(function(){
	$('.read-percent').each((function() {
		var percent = $(this).data('percent');
		var size = $(this).data('size');
		var read = $(this).data('read');

		var canvas = document.createElement('canvas');
		canvas.width = size;
		canvas.height = size;

		$(this).empty().append(canvas);
		
		makePie(canvas, percent, Math.floor(size / 2), read);
	}));
});
