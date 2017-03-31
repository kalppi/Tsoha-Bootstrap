
function toRadians(deg) {
    return deg * Math.PI / 180
}

function getColor(percent){
    var hue = (percent * 120).toString(10);
    return ['hsl(', hue, ',100%,50%)'].join('');
}

$(document).ready(function(){
	$('#threads canvas').each((function() {
		var percent = $(this).data('percent') / 100;

		var canvas = $(this).get(0);
		var ctx = canvas.getContext('2d');

		/*var color = 'red';

		if(percent >= 0.8) {
			color = 'green';
		} else if(percent >= 0.5) {
			color = 'yellow';
		}*/

		var color = getColor(percent);

		var cx = canvas.width / 2;
		var cy = canvas.height / 2;
		var radius = 18;

		/*ctx.beginPath();
		ctx.rect(0, 0, canvas.width, canvas.height);
		ctx.fillStyle = "red";
		ctx.fill();*/
/*
		ctx.beginPath();
		ctx.arc(cx, cy, radius, 0, 2 * Math.PI, false);
		ctx.fillStyle = color;
		ctx.fill();*/

		ctx.fillStyle = color;

		ctx.beginPath();
		ctx.moveTo(cx,cy);
		ctx.arc(cx,cy,20,toRadians(-90),toRadians(360 * percent - 90));
		ctx.lineTo(cx,cy);
		ctx.closePath();
		ctx.fill();

      /*ctx.lineWidth = 5;
      ctx.strokeStyle = '#003300';
      ctx.stroke();*/
	}));
});
