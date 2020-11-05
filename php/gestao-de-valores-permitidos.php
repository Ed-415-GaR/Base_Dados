<?php
session_start();
require_once("custom/php/common.php");

if(!is_user_logged_in())
{
   die("Tem que fazer o login");
}
// else
// {
//     echo 'Utilizador tem sessão iniciada'.'<br>';
// }
 if(!current_user_can("manage_allowed_values"))
 {
     die("Não tem autorização para aceder a esta página");
 }
 // else
 // {
 //     echo 'Autorização concedida'.'<br>';
 // }
 $ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);



 if(!$ligacao)
 {
     echo '<strong>Erro ao ligar à Base de dados</strong>'.mysqli_error($ligacao);
 }
 // else
 // {
 //     echo 'Ligação realizada com sucesso'.'<br>';
 // }

 $query_enum = "SELECT DISTINCT object.name, object.id FROM object,attribute
 WHERE attribute.obj_id = object.id  AND value_type = 'enum'"; // Query para obter id e nome do objeto que tenha value type enum

 $result_query_enum = mysqli_query($ligacao,$query_enum);

 if (!$result_query_enum) {
     die ("erro na query:".mysqli_error($ligacao));
   }


if(!isset($_REQUEST['estado']))
{

  if(!mysqli_num_rows($result_query_enum)>0)
  {
    echo 'Não há atributos especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) atributo(s) e depois voltar a esta opção.';
  }
  else
  {
    ?>
    <html>
    <body>
        <table class = "mytable" border width = "100%">
            <th><strong>Objeto</strong></th>
            <th>id</th>
            <th><strong>atributo</strong></th>
            <th>id</th>
            <th>valores permitidos</th>
            <th>estado</th>
            <th>ação</th>

  <?php

    while($row = mysqli_fetch_assoc($result_query_enum))
    {
        $nome_obj = $row['name'];
        $id_objeto = $row['id'];

          //seleciona os atributos que não têm valores permitidos associados
          $not_attribute = "SELECT * FROM attribute WHERE $id_objeto = attribute.obj_id AND value_type = 'enum' AND attribute.id  NOT IN
          (SELECT attr_allowed_value.attribute_id FROM attr_allowed_value)";
          $result_not = mysqli_query($ligacao, $not_attribute);

          $object_attribute_enum = "SELECT DISTINCT  attr_allowed_value.* FROM attribute, object, attr_allowed_value
          WHERE attr_allowed_value.attribute_id=attribute.id AND attribute.value_type='enum' AND  attribute.obj_id = $id_objeto";

          $result = mysqli_query($ligacao, $object_attribute_enum);

          if(!$result_not)
          {
            die("erro na query: ".mysqli_error($ligacao));
          }


        //buscar id e nome atributo
        $query_attribute = "SELECT DISTINCT * FROM attribute WHERE attribute.obj_id = $id_objeto AND value_type = 'enum'";
        $result_attribute = mysqli_query($ligacao,$query_attribute);

        if(!$result_attribute)
        {
          die("erro na query: ".mysqli_error($ligacao));
        }

        $linha = mysqli_num_rows($result);
        $num_rows_attr_NOT = mysqli_num_rows($result_not);

        $num_rows_soma = $linha + $num_rows_attr_NOT;
        //echo $num_rows_attr_NOT;
        //echo $linha;
        echo '<tr>';
        echo '<td rowspan = "'.$num_rows_soma.'">'.$nome_obj.'</td>';

        while($row = mysqli_fetch_assoc($result_attribute))
        {
          //vem da $query_attribute
          $id_atributo = $row['id'];
          $nome_atributo = $row['name'];

          //buscar value e id da tabela attr_allowed_value
          $query_valores_permitios = "SELECT * FROM attr_allowed_value WHERE $id_atributo = attr_allowed_value.attribute_id";
          $result_valores_permitidos = mysqli_query($ligacao,$query_valores_permitios);

          if(!$result_valores_permitidos)
          {
            die("erro na query: ".mysqli_error($ligacao));
          }

          $linha_valores_permitidos = mysqli_num_rows($result_valores_permitidos);

          if($linha_valores_permitidos == 0)
          {
?>
            <td><?php echo $id_atributo ?></td>
            <td><?php echo '<a href="gestao-de-valores-permitidos?estado=introducao&atributo='.$id_atributo.'"> ['.$nome_atributo.'] </a>'; ?></td>
            <td colspan="4"><center>Não há valores permitidos definidos</center></td>

            <tr>
<?php
          }
          else
          {

?>
          <td rowspan= "<?php echo $linha_valores_permitidos; ?>"><?php echo $id_atributo; ?></td>
          <td rowspan="<?php echo $linha_valores_permitidos; ?>"><?php echo '<a href="gestao-de-valores-permitidos?estado=introducao&atributo='.$id_atributo.'"> ['.$nome_atributo.'] </a>';?> </td>
<?php


              while($row_valores_permitidos = mysqli_fetch_assoc($result_valores_permitidos))
              {
                //vem da $query_valores_permitios
                $id_valores_permitidos = $row_valores_permitidos['id'];
                $value = $row_valores_permitidos['value'];
                $state = $row_valores_permitidos['state'];

                if($state == "active")
                {
                  $state = "ativo";
                }
                echo '<td>'.$id_valores_permitidos.'</td>';
                echo '<td>'.$value.'</td>';
                echo '<td>'.$state.'</td>';
                echo '<td>'."[editar][desativar]".'</td>';
                echo '</tr>';

              }

            }
      }

    }//fecha 1 while


 }
} //fecha estado
?>
        </table>
    </body>
  </html>
<?php


if($_REQUEST['estado']==introducao)
{
  $_SESSION['attribute_id']=$_REQUEST['atributo'];    //guarda id do atributo selecionado na tabela


?>

<h3>Gestão de valores permitidos - introdução</h3>

<form method="post">
Valor: <input type="text" name = "valor"> (obrigatório)
<input type="hidden" name = "estado" value = "inserir"><br><br>
<input type="submit" name = "submit" value = "Inserir valor permitido">
</form>
<?php
}

if($_REQUEST['estado']=='inserir')
{
?>

<h3>Gestão de valores permitidos - inserção</h3>

<?php
//------VALIDAÇÃO-------

  if(empty($_REQUEST['valor']))
  {
    echo "<br><strong>ATENÇÃO:</strong> Por favor insira um valor<br>";
    volta_atras();
    exit();
  }
//Ligação à BD
$ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

$nome_valor_permitido = $_REQUEST['valor'];
$attribute_id = $_REQUEST['atributo'];


$query_inserir = "INSERT INTO attr_allowed_value (attribute_id, value, state) VALUES ('$attribute_id', '$nome_valor_permitido', 'active')";
$result_query_inserir = mysqli_query($ligacao, $query_inserir);

if(!$query_inserir)
{
  die("erro na query: ".mysqli_error($ligacao));
}
else
{
  echo "<br><i>Inseriu os dados de novo valor permitido com sucesso.</i>";
}
?>
  <p style="color:grey" align="left"><i>Clique em <a href="gestao-de-valores-permitidos"><strong>Continuar</strong></a> para avançar.</i></p>

<?php

} //fecho estado = inserir

 ?>
