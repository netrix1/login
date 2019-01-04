<!DOCTYPE html>
<meta http-equiv="Content-Language" content="pt-br">
<html lang="pt-br">
<html>
<head>
	<title>Projeto Login Seguro</title>
</head>
<style type="text/css">
	body{
		margin:0px;
		padding: 0px;
	}
	.centro{
		margin:0 auto;
		width: 500px;
		padding: 30px;
		background-color: #eee;
	}
	.inpArea{
		right: 0px
	}
</style>
<body>
	<div class="centro">
		<h2>Login form</h2>
		<h3>
			<?php
			if (!empty($_COOKIE['msgerro'])){
				echo "<h4>";
				echo $_COOKIE['msgerro'];
				echo "</h4>";

				setcookie('msgerro', NULL);
			};
			?>
		<form id="login-form" class="form-horizontal" role="form" action="login.php" method="post">
			<p>Login: <input type="text" class="form-control" id="login" name="login" required placeholder="Informe seu login de usuário"></p>
			<p>Senha: <input type="password" class="form-control" id="pass" name="pass" required placeholder="Informe sua senha"></p>
			<p><center><input type="submit" name="Enviar" id="btn-login"></center></p>
		</form>
	</div>
</body>
</html>
<?php
/*

<form id="login-form" class="form-horizontal" role="form" action="login.php" method="post">             
    <div class="input-group margin-bottom-md">
        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
        <input type="email" class="form-control" id="email" name="email" required placeholder="Informe seu E-mail">                                        
    </div>
        
    <div class="input-group margin-bottom-md">
        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
        <input type="password" class="form-control" id="senha" name="senha" required placeholder="Informe sua Senha">
    </div>
            
    <div class="form-group margin-top-pq">
        <div class="col-sm-12 controls">
            <button type="button" class="btn btn-primary" name="btn-login" id="btn-login">
              Entrar
            </button>
        </div>
    </div> 

</form>    
*/ 

?>