<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\chat;
use strings;
?>

<h5 class="mb-0">Remote Conversations</h5>
<p class="text-muted font-italic">only for development purposes ...</p>

<div class="accordion" id="<?= $_accordion = strings::rand()  ?>"></div>
<script>
((_) => {
    let chatBox = ( u) => {
        fetch( _.url('<?= $this->route ?>/chatbox/<?= users::currentUser() ?>/'+String(u)))
        .then( data => data.text())
        .then( html => {
            let card = $(html);

            card.attr('data-id', u);

            $('.collapse', card).attr('data-parent', '#<?= $_accordion ?>');

            $('button[data-role="close"]', card).remove();


            card.appendTo('#<?= $_accordion ?>');

        });

    }

    chatBox(1);
    chatBox(2);
    chatBox(3);

})(_brayworth_);
</script>