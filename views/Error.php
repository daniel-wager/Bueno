<style type="text/css">
<!--
.bueno.error div,
.bueno.error p,
.bueno.error li,
.bueno.error td,
.bueno.error pre,
.bueno.error label {
	font: normal 12px arial;
	color: #600;
}
.bueno.error h1,
.bueno.error h2 {
	font: bold 15px arial;
	color: #600;
}
.bueno.error h2 {
	font-size: 12px;
}
.bueno.error label {
	font-size: 11px;
	font-weight: bold;
	padding-right: 5px;
}
.bueno.error div,
.bueno.error pre {
	padding: 15px;
}
.bueno.error .trace {
	font: normal 10px arial;
	color: #500;
}
.bueno.error div.header {
	white-space: nowrap;
	text-align: center;
}
.bueno.error div.header span {
	font: bold 14px arial;
}
.bueno.error div.header span span {
	font: normal 11px arial;
}
-->
</style>
<div class="bueno error">
	<div class="header">
		<span>
			you have problems
			<br />
			<span>(but knowing helps ;)</span>
		</span>
	</div>
	<div>
		<?=$this->body?>
	</div>
	<pre class="trace"><?=$this->trace?></pre>
</div>