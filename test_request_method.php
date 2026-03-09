<?php
if (isset($_SERVER['REQUEST_METHOD'])) {
    echo "Request method: " . $_SERVER['REQUEST_METHOD'];
} else {
    echo "REQUEST_METHOD is not set.";
}
?>
