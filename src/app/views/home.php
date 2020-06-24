<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/  ?>

<script>
$(document).ready( () => {
    ((_) => {
        let chat = $('<div class="position-absolute" style="bottom: 0; left: 0;"></div>').load( _.url('/chatbox'))
        $( 'footer').append( chat);

    })(_brayworth_);

});
</script>
