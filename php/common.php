<?php

function Get_enum_values($table_name, $column_name) {
    $ligacao = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
    $query = "
        SELECT COLUMN_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = '" . mysqli_real_escape_string($ligacao,$table_name) . "'
            AND COLUMN_NAME = '" . mysqli_real_escape_string($ligacao,$column_name) . "'
    ";
    $result = mysqli_query($ligacao,$query) or die (mysqli_error($ligacao));
    $row = mysqli_fetch_array($result);
    $enum_list = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));
    return $enum_list;
}


//Função Voltar atrás
function volta_atras()
{
echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
<noscript>
<a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
</noscript>";

}
?>
