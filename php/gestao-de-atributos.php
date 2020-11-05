<?php

require_once("custom/php/common.php");
//require_once("custom/css/ag.css");

if(!is_user_logged_in())
{
   die("Tem que fazer o login");
}
// else
// {
//     echo 'Utilizador tem sessão iniciada'.'<br>';
// }
 if(!current_user_can( "manage_attributes"))
 {
     die("Não tem autorização para aceder a esta página");
 }
 // else
 // {
 //     echo 'Autorização concedida'.'<br>';
 // }

 $ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);        //Connectar à BD

 $query = "SELECT * FROM attribute ORDER BY name";                      //construção da query
 $result = mysqli_query($ligacao,$query);                               // Execução da query

 if(!$ligacao)
 {
     echo '<strong>Erro ao ligar à Base de dados</strong>'.mysqli_error($ligacao);
 }
 // else
 // {
 //     echo 'Ligação realizada com sucesso1'.'<br>';
 // }

 if(!isset($_POST['estado']))
 {
     if(!mysqli_num_rows($result) > 0)
     {
         echo 'Não há propiedades especificadas';
     }
     else
     {


?>

<html>
<field>
    <body>
        <table class = "mytable" border width = "100%">
            <th><strong>Objeto</strong></th>
            <th><strong>id</strong></th>
            <th><strong>nome do atributo</strong></th>
            <th><strong>tipo de valor</strong></th>
            <th><strong>nome do campo no formulário</strong></th>
            <th><strong>tipo do campo no formulário</strong></th>
            <th><strong>tipo de unidade</strong></th>
            <th><strong>ordem do campo no formulário</strong></th>
            <th><strong>tamanho do campo no formulário</strong></th>
            <th><strong>obrigatório</strong></th>
            <th><strong>estado</strong></th>
            <th><strong>ação</strong></th>


<?php


        $query_objetos = "SELECT DISTINCT object.name, object.id FROM object, attribute WHERE attribute.obj_id = object.id";             //Seleciona nome, id da tabela objetos - Construção query
        $result_obj = mysqli_query($ligacao, $query_objetos);                                                                   //Execução query



        if(!$result_obj)
        {
            die ("erro na query:".mysqli_error($ligacao));
        }

        while ($row = mysqli_fetch_assoc($result_obj))
            {
                $id = $row['id'];
                $name = $row['name']; //nome objeto

                $query_atributos = "SELECT DISTINCT * FROM attribute WHERE attribute.obj_id = $id";
                $result_atributos = mysqli_query($ligacao, $query_atributos);

                if (!$result_atributos) {
                    die ("erro na query:".mysqli_error($ligacao));
                  }


                $linha = mysqli_num_rows($result_atributos);  //grava o numero de linhas que resultam da query

                    echo '<tr>';
                    echo '<td rowspan = "'.$linha.'">'.$name.'</td>';
                    //echo '<td>'.$id.'</td>';
                    //echo '</tr>';

                    while ($row_atributo = mysqli_fetch_assoc($result_atributos))
                    {
                        $id_atributo = $row_atributo['id'];
                        $nome_atr = $row_atributo['name'];
                        $tipo_valor = $row_atributo['value_type'];
                        $nome_formulario = $row_atributo['form_field_name'];
                        $tipo_formulario = $row_atributo['form_field_type'];
                        $unit_type_id = $row_atributo['unit_type_id'];
                        $ordem_formulario = $row_atributo['form_field_order'];
                        $tamanho_formulario = $row_atributo['form_field_size'];
                        $obrigatorio = $row_atributo['mandatory'];
                        $estado = $row_atributo['state'];

                        $query_unidades = "SELECT * FROM attr_unit_type WHERE attr_unit_type.id = $unit_type_id";
                        $result_unidades = mysqli_query($ligacao, $query_unidades);

                        // if (!$result_unidades) {
                        //     die ("erro na query1:".mysqli_error($ligacao));
                        //   }

                        $row_unidades = mysqli_fetch_assoc($result_unidades);

                        $name_unidades = $row_unidades['name'];         //buscar o nome da unidade da tabela attr_unit_type


                        if($obrigatorio == 1)
                        {
                            $obrigatorio = "sim";
                        }
                        else
                        {
                            $obrigatorio = "não";
                        }

                        echo '<td>'.$id_atributo.'</td>';
                        echo '<td>'.$nome_atr.'</td>';
                        echo '<td>'.$tipo_valor.'</td>';
                        echo '<td>'.$nome_formulario.'</td>';
                        echo '<td>'.$tipo_formulario.'</td>';
                        echo '<td>'.$name_unidades.'</td>';
                        echo '<td>'.$ordem_formulario.'</td>';
                        echo '<td>'.$tamanho_formulario.'</td>';
                        echo '<td>'.$obrigatorio.'</td>';
                        echo '<td>'.$estado.'</td>';
                        echo '<td>'."[editar] [desativar]".'</td>';
                        echo '</tr>';



                    }

            }



    }


?>

        </table>
</field>
    </body>


<?php //FORMULÁRIO?>

<br><h3><center>Gestão de atributos - introdução</center></h3>

<form method="post">
<i>Nome do Atributo: </i>
<input type="text" name = "nome_atributo" > (obrigatório)<br><br>

<i>Tipo de Valor: (obrigatório)</i><br>
<?php

$atributos = "attribute";   //nome da tabela
$valores = "value_type";    //atributo da tabela - mesmo nome na BD
$tipos_valores = Get_enum_values($atributos,$valores);

if (!$tipos_valores) {
  die("erro".mysqli_error($ligacao));
}

foreach ($tipos_valores as $key => $value) {        //array associativo
  echo "<input type='radio' name='tipo_valor' value=".$value.">".$value."<br>";
}
?>


<br><i>objeto a que irá pertencer este atributo - (obrigatório)</i>
<?php

    $query_obj_name = "SELECT * FROM object";
    $result_obj_name = mysqli_query($ligacao,$query_obj_name);

    if(!$result_obj_name)
    {
        die ("erro na query objetos".mysqli_error($ligacao));
    }
        echo "<select name = 'objetos'>";

        while($row_name = mysqli_fetch_assoc($result_obj_name))
        {

            $id_obj = $row_name['id'];
            $nome_objeto = $row_name['name'];
            echo "<option value = '".$id_obj."'> $nome_objeto </option>";
        }

        echo "</select><br><br>";



?>

<br<br><i>Tipo do campo do formulário: (obrigatório)</i><br>
<?php

$atributos = "attribute";                   //nome da tabela
$campo_formulario = "form_field_type";    //atributo da tabela - mesmo nome na BD
$tipos_campo = Get_enum_values($atributos,$campo_formulario);

if (!$tipos_campo) {
  die("erro".mysqli_error($ligacao));
}

foreach ($tipos_campo as $key => $value) {
  echo "<input type='radio' name='tipo_formulário' value=".$value.">".$value."<br>";
}
?>


<br><i>Tipo de unidade - (opcional)</i>
<?php

$query_unit_type = "SELECT * FROM attr_unit_type";
$result_unit_type = mysqli_query($ligacao,$query_unit_type);


if($result_unit_type)
{
    echo "<select name = 'tipos_de_unidades'>";
    echo "<option value = 'NULL'></option>";
    while($row_unit_type = mysqli_fetch_assoc($result_unit_type))
    {
        $id_unidade = $row_unit_type['id'];
        $unit_type = $row_unit_type['name'];
        echo "<option value = '".$id_unidade."'> $unit_type </option>";

    }
    echo "</select><br><br>";
}
else
{
    die ("Erro na query:".mysqli_error($ligacao));
}

?>


<i>Ordem do campo no formulário: </i>
<input type ="text" size = "6" name = "ordem_formulario"> (obrigatório e um número superior a 0)<br><br>

<i>Tamanho do campo no formulário: </i>
<input type ="text" size = "6" name = "tamanho_formulario">(obrigatório no caso de o tipo de campo ser text ou textbox)<br><br>

<i>Obrigatório: (obrigatório)</i><br>
<input type="radio" name = "obrigatorio" value = "1">sim<br>
<input type="radio" name = "obrigatorio" value = "2">não<br><br>


<i>objeto referenciado por este atributo - (opcional)</i>
<?php

    $query_objects = "SELECT * FROM object";
    $result_objects = mysqli_query($ligacao,$query_objects);

    if($result_objects)
    {
        echo "<select name = 'objeto_ref'>";
        echo "<option value = 'NULL'></option>";
        while($row_objects = mysqli_fetch_assoc($result_objects))
        {
            $id_objeto = $row_objects['id'];
            $objects = $row_objects['name'];
            echo "<option value = '".$id_objeto."'> $objects </option>";

        }

        echo "</select><br><br>";
    }
    else
    {
        die ("Erro na query:".mysqli_error($ligacao));
    }
?>

<input type="hidden" name = "estado" value="inserir"><br>
<input type="submit" name = "submit" value="Inserir atributo">

</form>

<?php
}   //fecha o estado

if($_POST['estado']=='inserir')
{
 ?>
      <h3><strong>Gestão de atributos - inserção</strong></h3>
<?php

    //VALIDAÇÕES

    if(empty($_POST['nome_atributo']))              //Verifica se está em branco
    {
        echo "<br><strong>ATENÇÃO:</strong>Nome do atributo é obrigatório<br>";
        volta_atras();
        exit();

    }

    if(empty($_POST['tipo_valor']))
    {
        echo "<br><strong>ATENÇÃO:</strong> Tipo de Valor é obrigatório<br>";
        volta_atras();
        exit();
    }

    if(empty($_POST['ordem_formulario']))              //Verifica se está em branco
    {
        echo "<br><strong>ATENÇÃO:</strong>Ordem do campo no formulário é obrigatório<br>";
        volta_atras();
        exit();
    }


    if(($_POST['ordem_formulario'])==0 || !is_numeric($_POST['ordem_formulario']))             //Verifica se é superior a zero, se não for mostra mensagem erro
    {
        echo "<br><strong>ATENÇÃO:</strong>Por favor insira um número superior a zero na Ordem do campo no formulário<br>";
        volta_atras();
        exit();
    }


    if($_POST['tipo_formulário']=="text")         //Verifica se é text

    {
        if(empty($_POST['tamanho_formulario']) || !is_numeric($_POST['tamanho_formulario']))              //Verifica se está em branco e se é um numero
        {
            echo "<br><strong>ATENÇÃO:</strong>Por favor insira um número no Tamanho do campo no formulário!<br>";
            volta_atras();
            exit();
        }
    }


    if(empty($_POST['obrigatorio']))
    {
        echo "<br><strong>ATENÇÃO:</strong> Obrigatório é obrigatório<br>";
        volta_atras();
        exit();
    }

    if(empty($_POST['tipo_formulário']))
    {
        echo "<br><strong>ATENÇÃO:</strong> Tipo do campo do formulário é obrigatório<br>";
        volta_atras();
        exit();
    }

    // //Verifica se tem o formato aaxbb
       if ($_POST['tipo_formulário'] == "textbox" && !preg_match("/^[1-9]{1}x[1-9]{1}$/", $_POST['tamanho_formulario']))
       {
         echo "<br><strong>ATENÇÃO:</strong> Tamanho do campo no formulário deve respeitar o seguinte formato aaxbb, em que <strong>aa</strong> é o número de colunas e <strong>bb</strong> o número de linhas<br>";
         volta_atras();
         exit();
       }



$nome_atributo = $_POST['nome_atributo'];
$tipo_valor = $_POST['tipo_valor'];
$objetos_id = $_POST['objetos'];
$tipo_formulário = $_POST['tipo_formulário'];
$tipos_de_unidades = $_POST['tipos_de_unidades'];
$ordem_formulario = $_POST['ordem_formulario'];
$tamanho_formulario = $_POST['tamanho_formulario'];
$obrigatorio = $_POST['obrigatorio'];
$objeto_ref = $_POST['objeto_ref'];

/*

echo $nome_atributo.'<br>';
echo $tipo_valor.'<br>';
echo $objetos_id.'<br>';
echo $tipo_formulário.'<br>';
echo $tipos_de_unidades.'<br>';
echo $ordem_formulario.'<br>';
echo $tamanho_formulario.'<br>';
echo $obrigatorio.'<br>';
echo $objeto_ref.'<br>';
*/

$ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);


$query_objetos = "SELECT name FROM object WHERE id = $objetos_id";
$result_query_objetos = mysqli_query($ligacao, $query_objetos);

if (!$result_query_objetos) {
    die ("erro na query:".mysqli_error($ligacao));
  }

$row = mysqli_fetch_assoc($result_query_objetos);
$nome_obj = $row['name'];


$concatenar = preg_replace('/[^a-z0-9_ ]/i', '', $nome_atributo);
$concatenar_nome =substr($nome_obj,0,3);     //retorna uma parte especifica de uma string
//$form_field_name = "";

if($tipos_de_unidades != 'NULL')
{

$query_inserir_attr = "INSERT INTO attribute (name, obj_id, value_type, form_field_name, form_field_type, unit_type_id, form_field_size, form_field_order, mandatory, state, obj_fk_id)
VALUES ('$nome_atributo', '$objetos_id', '$tipo_valor', '$form_field_name', '$tipo_formulário', '$tipos_de_unidades', '$tamanho_formulario', '$ordem_formulario', '$obrigatorio', 'active', ".$objeto_ref.")";

$result_query_inserir_attr = mysqli_query($ligacao,$query_inserir_attr);

if (!$result_query_inserir_attr) {
    die ("erro na query1:".mysqli_error($ligacao));
  }

    $id = mysqli_insert_id($ligacao);       //id do atributo
    $form_field_name=$concatenar_nome."-".$id."-".$concatenar;

    $query_udapte_attr = "UPDATE attribute SET form_field_name='$form_field_name' WHERE id = '$id' ";   //query que atualiza form_field_name na tabela attribute
    $result_update_attr = mysqli_query($ligacao,$query_udapte_attr);

    if (!$result_update_attr) {
        die ("erro na query".mysqli_error($ligacao));
      }
      else
    {
    echo "<i>Inseriu os dados de novo tipo de unidade com sucesso.</i>";
    }
    ?>

    <p style="color:grey" align="left"><i>Clique em <a href="gestao-de-atributos"><strong>Continuar</strong></a> para avançar.</i></p>

<?php


}

elseif($tipos_de_unidades == 'NULL')
{

    $query_inserir_attr2 = "INSERT INTO attribute (name, obj_id, value_type, form_field_name, form_field_type, form_field_size, form_field_order, mandatory, state, obj_fk_id)
    VALUES ('$nome_atributo', '$objetos_id', '$tipo_valor', '$form_field_name', '$tipo_formulário', '$tamanho_formulario', '$ordem_formulario', '$obrigatorio', 'active', ".$objeto_ref.")";

    $result_query_inserir_attr2 = mysqli_query($ligacao,$query_inserir_attr2);

if (!$result_query_inserir_attr2) {
    die ("erro na query22:".mysqli_error($ligacao));
  }

  $id = mysqli_insert_id($ligacao);                       //id do atributo
  $form_field_name=$concatenar_nome."-".$id."-".$concatenar;

  $query_udapte_attr2 = "UPDATE attribute SET form_field_name='$form_field_name' WHERE id = '$id' ";   //query que atualiza form_field_name na tabela attribute
  $result_update_attr2 = mysqli_query($ligacao,$query_udapte_attr2);

  if (!$result_update_attr2) {
      die ("erro na query".mysqli_error($ligacao));
    }
    else
  {
  echo "<i>Inseriu os dados de novo tipo de unidade com sucesso.</i>";
  }
  ?>
  <p style="color:grey" align="left"><i>Clique em <a href="gestao-de-atributos"><strong>Continuar</strong></a> para avançar.</i></p>

<?php
}

} //fecha estdo Inserir

?>
