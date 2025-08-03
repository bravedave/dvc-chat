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

use bravedave\dvc\strings;

?>

<h5 class="mb-0">Remote Conversations</h5>
<p class="text-muted font-italic">only for development purposes ...</p>

<div class="accordion" id="<?= $_accordion = strings::rand() ?>"></div>
<script type="module">
  const _ = _brayworth_;
  const accordion = $('#<?= $_accordion ?>');

  const chatBox = u => {

    const url = _.url('<?= $this->route ?>/chatbox/<?= users::currentUser() ?>/' + String(u));
    // console.log('chatBox', url);
    fetch(url)
      .then(data => data.text())
      .then(html => {

        // console.log(html);

        const card = $(html);
        card.attr('data-id', u);

        card.find('.collapse').attr('data-parent', '#<?= $_accordion ?>');

        $('button[data-role="close"]', card).remove();
        card.appendTo('#<?= $_accordion ?>');
      });
  }

  chatBox(1);
  chatBox(2);
  chatBox(3);
</script>