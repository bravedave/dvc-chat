<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/  ?>

<div id="<?= $_uid = strings::rand()  ?>"></div>
<script>
((_) => {

    $('<div></div>').appendTo('#<?= $_uid ?>').load( _.url('/chatbox/0/1'))
    $('<div></div>').appendTo('#<?= $_uid ?>').load( _.url('/chatbox/0/2'))
    $('<div></div>').appendTo('#<?= $_uid ?>').load( _.url('/chatbox/0/3'))

})(_brayworth_);
</script>