<?php
switch (JRequest::getCmd('task'))
{
	case basicMap2:
		basicMap2();
		break;
	default:
		basicMap();
		break;
}
function basicMap()
{
echo "2 here";
}
function basicMap2()
{
echo "1 here";
}
?>

