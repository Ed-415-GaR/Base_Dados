<?php
require_once("custom/php/common.php");

$ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,bitnami_wordpress);

	if(!is_user_logged_in()){
  die("Tem que fazer o login");
	}
	if(!current_user_can("insert_values"))
	{
  die("Não tem autorização para aceder a esta página");
	}

	if($_REQUEST['estado'] == ""){
	?>
	<h3><center>Inserção de valores - escolher objeto/formulário customizado</center></h3>

	<b>  objetos: </b>
	<ul>
	<?php
	$query_objetotipo = "SELECT name, id
						FROM obj_type
						ORDER BY id";
	$result_objetotipo = mysqli_query($ligacao,$query_objetotipo);
		while($lista_objetotipo = mysqli_fetch_assoc($result_objetotipo)){
		?> <li> <?php
		echo '<label>'.$lista_objetotipo['name'].'</label>';?> </li>
		<?php

	$query_objeto = "SELECT DISTINCT id, name
					FROM object
					WHERE obj_type_id = " . $lista_objetotipo["id"] . "
					ORDER BY id";

	$result_objeto = mysqli_query($ligacao,$query_objeto);
	?> <ul>
	<?php
			while($lista_objeto = mysqli_fetch_assoc($result_objeto)){
	?> <li>
		<a href=insercao-de-valores?estado=introducao&obj=<?php echo $lista_objeto['id']?> > [<?php echo $lista_objeto['name']?>]</a>
		</li><?php
							}
		?> </ul>
		<?php

				}
	?>
	</ul>
	<b> Formulários customizados:</b> <ul>
	<?php
		$query_form = "SELECT id, name
					   FROM custom_form
					   ORDER BY id";
		$result_form = mysqli_query($ligacao,$query_form);
		while($lista_form = mysqli_fetch_assoc($result_form))
	{
				?> <li>
		<a href=insercao-de-valores?estado=introducao&form=<?php echo $lista_form['id']?> > [<?php echo $lista_form['name']?>]</a>
		</li> <?php
	}

	?>
	</ul>
	<?php
	}
	if($_REQUEST['estado']=="introducao"){

		if(isset($_REQUEST['obj']))	{

	$_SESSION['obj_id'] = $_REQUEST['obj'];

	$objet_id = $_SESSION['obj_id'];

	$query_objet_name = "SELECT name
						 FROM object
						 WHERE id =	".$objet_id."";

	$result_objet_name = mysqli_query($ligacao,$query_objet_name);

	$array_objet_name = mysqli_fetch_assoc($result_objet_name);

	$_SESSION['obj_name'] = $array_objet_name['name'];

	$objet_name = $_SESSION['obj_name'];

	$query_objet_typeid = "SELECT obj_type.id
						   FROM object, obj_type
						   WHERE object.obj_type_id = obj_type.id AND object.id = ".$objet_id."";

	$result_objet_typeid = mysqli_query($ligacao,$query_objet_typeid);

	$array_objet_typeid = mysqli_fetch_assoc($result_objet_typeid);

	$_SESSION['obj_type_id'] = $array_objet_typeid['obj_type_id'];

	$objet_type_id = $_SESSION['obj_type_id'];

		//formulario dinamico
	?>
	<h3><center>Inserção de valores - <?php echo $objet_name ?> </center></h3>

	<form action = "/insercao-de-valores?estado=validar&obj=<?php echo $objet_id ?>" method = "POST" name = "obj_type_<?php echo $objet_type_id ?>_obj_<?php echo $objet_id ?>">

	<?php
		$query_atrib_ativo = "SELECT *
						   FROM attribute
						   WHERE obj_id = ".$objet_id." AND state = 'active'";

		$result_atrib_ativo = mysqli_query($ligacao,$query_atrib_ativo);
	if(mysqli_num_rows($result_atrib_ativo) > 0){

	while($atrib_ativo = mysqli_fetch_assoc($result_atrib_ativo))
	{
		$atrib_id = $atrib_ativo['id'];
		$query_attr_unit_type = "SELECT attr_unit_type.name
								 FROM attr_unit_type, attribute
								 WHERE attr_unit_type.id = attribute.unit_type_id AND attribute.id = ".$atrib_id."";

		$result_attr_unit_type = mysqli_query($ligacao,$query_attr_unit_type);
		$array_attr_unit_type = mysqli_fetch_assoc($result_attr_unit_type);
			if(!empty($array_attr_unit_type)){
 					?> <li> <?php echo $atrib_ativo['name']; ?> - Tipo de Unidade: <?php echo $array_attr_unit_type['name'];?> <br>  <?php
 			}else {
           ?><li> <?php echo $atrib_ativo['name'];?> : <br> <?php
			}

		switch($atrib_ativo['value_type'])
			{
			case "text":
				if($atrib_ativo['form_field_type'] == "text")
					{
					 ?> <input type = "text" name = "<?php echo $atrib_ativo['form_field_name']; ?>" >   <br>
				<?php		}
				elseif($atrib_ativo['form_field_type'] == "textbox")
					{

					 ?>  <input type = "textbox" name = "<?php echo $atrib_ativo['form_field_name']; ?>" > <br>

				<?php		}

				break;

			case "bool":

				 ?> <br>
					<input type = "radio" name = "<?php echo $atrib_ativo['form_field_name']; ?>" value = "active" > Ativo<br>
					<input type = "radio" name = "<?php echo $atrib_ativo['form_field_name']; ?>" value = "inactive"> Inativo <br>

						<?php
			break;

			case "int":
			case "double":

				 ?>  <input type = "text" name = "<?php echo $atrib_ativo['form_field_name']; ?>" >
					<?php
			break;

			case "enum":
				$query_attr_allowed = "SELECT *
									FROM attr_allowed_value
									WHERE attribute_id = ".$atrib_ativo["id"]."";
				$result_attr_allowed = mysqli_query($ligacao,$query_attr_allowed);

		while($array_attr_allowed = mysqli_fetch_assoc($result_attr_allowed)){
			if($atrib_ativo["form_field_type"] == "radio"){
				?>
				<input type = "radio" name = <?php echo $atrib_ativo["form_field_name"]; ?> value = <?php echo $array_attr_allowed["value"]; ?> > <?php echo $array_attr_allowed["value"]; ?> <br>
					<?php
					}

			elseif($atrib_ativo["form_field_type"] == "checkbox")
					{?>
				<input type = "checkbox" name = <?php echo $atrib_ativo["form_field_name"]; ?> value = <?php echo $array_attr_allowed["value"]; ?> > <?php echo $array_attr_allowed["value"]; ?> <br>
					<?php

					}
			elseif($atrib_ativo["form_field_type"] == "selectbox")
					{?>
					<select name = " <?php echo $atrib_ativo['form_field_name'];	?>">  <br>

					<option value = <?php echo $array_attr_allowed["value"]; ?> >	</option>
					<?php

					}
					}
			break;

			case "obj_ref":
				$query_obj_inst = "SELECT DISTINCT obj_inst.id, obj_inst.object_name
								   FROM obj_inst, attribute, object
								   WHERE obj_inst.object_id = ".$objet_id." AND attribute.obj_fk_id = ".$objet_id." ";

				$result_obj_inst = mysqli_query($ligacao,$query_obj_inst);

				if (mysqli_num_rows($result_obj_inst) > 0) {
					?>
					<select name = " <?php echo $atrib_ativo['form_field_name'];?>"> <br>


		<?php	while($array_obj_inst = mysqli_fetch_assoc($result_obj_inst))
			{	?>
				<br>
				<option value = <?php echo $array_obj_inst['id'];?>> <?php echo $array_obj_inst['object_name'];?>	</option> <br>
		<?php	}
			}
			break;
			}
		}
	}		else
{
	echo "Não existem atributos associados a este objeto!"; ?> <br> <?php
	}
			?>
			Nome para instância do objeto: (opcional)<br>
			<input type = "text" name ="obj_inst_name" > <br>
			<input type="hidden" name="estado" value="validar">
			<input type ="submit" value = "Submeter">
			</form>
		<?php
	}	elseif(isset($_REQUEST['form']))
		{
		$_SESSION['form_id'] = $_REQUEST['form'];

		$form_id = $_SESSION['form_id'];

		$query_form_name = "SELECT name
						 FROM custom_form
						 WHERE id =	".$form_id."";

	$result_form_name = mysqli_query($ligacao,$query_form_name);

	$array_form_name = mysqli_fetch_assoc($result_form_name);

	$_SESSION['form_name'] = $array_form_name['name'];

	$form_name = $_SESSION['form_name'];

	//formulario dinamico
	?>
	<h3><center>Inserção de valores - <?php echo $form_name; ?> </center></h3>

	<form action = "/insercao-de-valores?estado=validar&form=<?php echo $form_id; ?>" method = "POST" name = "obj_<?php echo $form_id ?>">

<?php
	$query_atrib_ativo_form = "SELECT *
					   FROM attribute, custom_form_has_attribute
					   WHERE attribute.id = custom_form_has_attribute.attribute_id
					   AND custom_form_has_attribute.custom_form_id =".$form_id." AND attribute.state = 'active'";

	$result_atrib_ativo_form = mysqli_query($ligacao,$query_atrib_ativo_form);

	if(mysqli_num_rows($result_atrib_ativo_form) > 0){

		while($atrib_ativo_form = mysqli_fetch_assoc($result_atrib_ativo_form))
	{
		$atrib_id_form = $atrib_ativo_form['id'];
		$query_attr_unit_type_form = "SELECT attr_unit_type.name
								 FROM attr_unit_type, attribute
								 WHERE attr_unit_type.id = attribute.unit_type_id AND attribute.id = ".$atrib_id_form."";

		$result_attr_unit_type_form = mysqli_query($ligacao,$query_attr_unit_type_form);
		$array_attr_unit_type_form = mysqli_fetch_assoc($result_attr_unit_type);
			if(!empty($array_attr_unit_type_form)){
 					?> <li> <?php echo $atrib_ativo_form['name']; ?> - Tipo de Unidade: <?php echo $array_attr_unit_type_form['name'];?>  <?php
 			}else {
           ?><li> <?php echo $atrib_ativo_form['name'];?> : <?php
			}

		switch($atrib_ativo_form['value_type'])
			{
			case "text":
				if($atrib_ativo_form['form_field_type'] == "text")
					{
					 ?><br> <input type = "text" name = "<?php echo $atrib_ativo_form['form_field_name']; ?>" >
				<?php		}
				elseif($atrib_ativo_form['form_field_type'] == "textbox")
					{

					 ?> <br> <input type = "textbox" name = "<?php echo $atrib_ativo_form['form_field_name']; ?>" >

				<?php		}

				break;

			case "bool":

				 ?> <br>
					<input type = "radio" name = "<?php echo $atrib_ativo_form['form_field_name']; ?>" value = "active" > Ativo<br>
					<input type = "radio" name = "<?php echo $atrib_ativo_form['form_field_name']; ?>" value = "inactive"> Inativo <br>

						<?php
			break;

			case "int":
			case "double":

				 ?> <br> <input type = "text" name = "<?php echo $atrib_ativo_form['form_field_name']; ?>" >
					<?php
			break;

			case "enum":
				$query_attr_allowed_form = "SELECT *
									FROM attr_allowed_value
									WHERE attribute_id = ".$atrib_ativo_form["id"]."";
				$result_attr_allowed_form = mysqli_query($ligacao,$query_attr_allowed_form);

		while($array_attr_allowed_form = mysqli_fetch_assoc($result_attr_allowed_form)){
			if($atrib_ativo_form["form_field_type"] == "radio"){
				?>
				<br>
				<input type = "radio" name = <?php echo $atrib_ativo_form["form_field_name"]; ?> value = <?php echo $array_attr_allowed_form["value"]; ?> > <?php echo $array_attr_allowed_form["value"]; ?> <br>
					<?php
					}

			elseif($atrib_ativo_form["form_field_type"] == "checkbox")
					{?>
					<br>
				<input type = "checkbox" name = <?php echo $atrib_ativo_form["form_field_name"]; ?> value = <?php echo $array_attr_allowed_form["value"]; ?> > <?php echo $array_attr_allowed_form["value"]; ?> <br>
					<?php

					}
			elseif($atrib_ativo_form["form_field_type"] == "selectbox")
					{?>
					<select name = " <?php echo $atrib_ativo_form['form_field_name'];	?>">  <br>

					<option value = <?php echo $array_attr_allowed_form["value"]; ?> >	</option>
					<?php

					}
				}
			break;
			}
	}
			?> <br>
			<input type ="submit" value = "Submeter">
			</form>
		<?php
	} else
	{
		echo "Não existem atributos associados a este form!";
	}
	}
	}
	elseif($_REQUEST['estado']=="validar")
	{	if(isset($_REQUEST['obj']))
		{?>
		<h3><center>Inserção de valores - <?php echo $_SESSION['obj_name'] ?> - validar </center></h3>
		<form
		action = "/insercao-de-valores?estado=inserir&obj=<?php echo $_SESSION['obj_id'] ?>"
		method = "POST" >
		<?php

		$inseriu = 1;
		$query_atrib_ativo = "SELECT *
						   FROM attribute
						   WHERE obj_id = ".$_SESSION['obj_id']." AND state = 'active'";

		$result_atrib_ativo = mysqli_query($ligacao,$query_atrib_ativo);
		if(mysqli_num_rows($result_atrib_ativo) == 0){
			echo "Não há objetos";
		}else
		{
	while($atrib_ativo = mysqli_fetch_assoc($result_atrib_ativo))
		{
		if(empty($_REQUEST[$atrib_ativo['form_field_name']]))
		{
			$inseriu = 0;
			echo "É obrigatório o preenchimento do campo: ".$atrib_ativo['name'].""; ?> <br> <?php

			}

		}

		if($inseriu == 1){
				?>
		Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos.
			<br>
			<?php $query_atrib_ativo2 = "SELECT *
						   FROM attribute
						   WHERE obj_id = ".$_SESSION['obj_id']." AND state = 'active'";
			$result_atrib_ativo2 = mysqli_query($ligacao,$query_atrib_ativo2);

			while($atrib_ativo2 = mysqli_fetch_assoc($result_atrib_ativo2)){
			if(isset($_REQUEST[$atrib_ativo2["form_field_name"]])){
       	?>
			<input type = "hidden" name =  <?php echo $atrib_ativo2['form_field_name']; ?> value = <?php echo $_REQUEST[atrib_ativo2['form_field_name']]; ?> >
			<?php  echo $_REQUEST[$atrib_ativo2["form_field_name"]];    ?> <br><?php

			}

		}
	}
			volta_atras(); ?><br>
			<input type="hidden" name="estado" value="inserir">
			<br>
			<?php
		}
		if($inseriu == 1){?>
			<input type ="submit" value = "Submeter">	<?php
		}  ?>
			</form> <?php
	}
		elseif(isset($_REQUEST['form'])){
?>
				<h3><i><center>Insercao de valores - <?php echo $_SESSION['form_name']; ?> - validar</center></i></h3>

				<form
				method = "POST"
				action = "?insercao-de-valores?estado=inserir&form=<?php echo $_SESSION['form_id']; ?>" >

<?php
				$inseriu = 1;
				$query_attribute_form = "SELECT attribute.* FROM attribute, custom_form_has_attribute
										WHERE attribute.id = custom_form_has_attribute.attribute_id
										AND custom_form_has_attribute.custom_form_id = ".$_SESSION['form_id']."";

				$result_attribute_form = mysqli_query($ligacao, $query_attribute_form);
			while($array_forms = mysqli_fetch_assoc($result_attribute_form)){
				if(empty($_REQUEST[$array_forms['form_field_name']]))	{

				$inseriu = 0;
				echo "É obrigatório o preenchimento do campo: ".$array_forms['name'].""; ?> <br> <?php

			}

		}

				if($inseriu == 1)
				{
?>
 	Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos.
<?php
					$query_attribute_form2 = "SELECT attribute.* FROM attribute, custom_form_has_attribute
											WHERE attribute.id = custom_form_has_attribute.attribute_id
											AND custom_form_has_attribute.custom_form_id = ".$_SESSION['form_id']." ";

					$result_attribute_form2 = mysqli_query($ligacao, $query_attribute_form2);
				}
				?> <br> <?php
				volta_atras();
?>				<input type="hidden" name="estado" value="inserir">
<?php
				if($inseriu == 1)
				{
?>
					<br>
					<p>
					<input type="submit" value="Submeter">
					</p>
<?php
				}
?>
				</form>

		<?php
				}

		}

	elseif($_REQUEST['estado']=="inserir")
	{
		?>
		<h3><center>Inserção de valores - <?php echo $_SESSION['obj_name'] ?> - inserção </center></h3>
	<?php
	$insere_obj_inst = "INSERT INTO obj_inst (object_id) VALUES('" . $_SESSION['obj_id'] . "')";
	$result_insere_obj_inst = mysqli_query($ligacao,$insere_obj_inst);
	$obj_inst_id = mysql_insert_id();

	//user_login – Inserir o nome de usuário para aceder o WP
	$dia=date("Y-m-d");
	$hora=time("H:i:s");
	$utilizador_atual = wp_get_current_user();


	$query_atrib_ativo = "SELECT *
						   FROM attribute
						   WHERE obj_id = ".$_SESSION['obj_id']." AND state = 'active'";
	$result_atrib_ativo = mysqli_query($ligacao,$query_atrib_ativo);

	while($atrib_ativo=mysqli_fetch_assoc($result_atrib_ativo)){
	$query="INSERT INTO value(obj_inst_id, attr_id, value, date, time, producer)
						VALUES('".$obj_inst_id."','".$atrib_ativo['id']."','".$_REQUEST[$row_attr["form_field_name"]]."','".$dia."','".$hora."','".$utilizador_atual."')";

		}
	}
	?>
