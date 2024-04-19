<?php

namespace App\Services;

class webhookService
{
    function trata_ack($id_message, $timestamp, $status)
    {      

        // atualiza em histórico de conversas individual
        switch ($status) {
            case 'sent':
                $sql = "update tbl_conversas set time_sent = '$timestamp' where id_message = '$id_message'";
                break;
            case 'delivered':
                $sql = "update tbl_conversas set time_delivered = '$timestamp' where id_message = '$id_message'";
                break;
            case 'read':
                $sql = "update tbl_conversas set time_read = '$timestamp' where id_message = '$id_message'";
                break;
        }
        $Dbobj = new dbconnection();
        $query = mysqli_query($Dbobj->getdbconnect(), $sql);
        mysqli_close($Dbobj->$conn);

        // atualiza em histórico de promoções
        switch ($status) {
            case 'sent':
                $sql = "update tbl_promos_historico set time_sent = '$timestamp' where id_message = '$id_message'";
                break;
            case 'delivered':
                $sql = "update tbl_promos_historico set time_delivered = '$timestamp' where id_message = '$id_message'";
                break;
            case 'read':
                $sql = "update tbl_promos_historico set time_read = '$timestamp' where id_message = '$id_message'";
                break;
        }
        $Dbobj = new dbconnection();
        $query = mysqli_query($Dbobj->getdbconnect(), $sql);
        mysqli_close($Dbobj->$conn);
    }
}
