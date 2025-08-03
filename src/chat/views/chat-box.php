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

$sendOnEnter = true;
// $sendOnEnter = false;

?>

<div class="card m-1" style="width: 18rem;">
  <div class="card-header p-0 d-flex">
    <button type="button" class="btn btn-light flex-fill text-left"
      data-bs-toggle="collapse"
      data-bs-target="#<?= $_collapse = strings::rand() ?>">
      <?php
      if (0 == $remote->id) {

        printf(
          '%s => %s',
          $local->name,
          $remote->name
        );
      } else {

        print $remote->name;
      }   ?>
      <span class="badge badge-light d-none" id="<?= $_unseen = strings::rand() ?>">&nbsp;</span>
      <span class="spinner-grow spinner-grow-sm text-danger d-none" id="<?= $_unseen ?>flash"></span>
    </button>

    <?php if ($remote->id) {  ?>

      <button type="button" class="btn btn-light"
        data-id="<?= $remote->id ?>"
        id="<?= $_report = strings::rand() ?>">
        <i class="bi bi-window"></i>
      </button>
    <?php } ?>

    <button type="button" class="btn btn-light"
      data-role="close"
      id="<?= $_close = strings::rand() ?>">
      <i class="bi bi-x"></i>
    </button>
  </div>

  <div class="collapse fade" id="<?= $_collapse ?>">
    <form id="<?= $_form = strings::rand() ?>">
      <input type="hidden" name="action" value="post" />
      <input type="hidden" name="remote" value="<?= $remote->id ?>" />
      <input type="hidden" name="local" value="<?= $local->id ?>" />
      <input type="hidden" name="version" value="0" />

      <div class="card-body p-1 overflow-auto" style="height: 40vh">
        <div class="container-fluid js-messages"></div>
      </div>

      <div class="card-footer p-1">

        <div class="p-2 d-none position-relative" role="status">

          <span class="position-absolute text-muted"></span>
          <div class="spinner-border mx-auto my-2"></div>
        </div>

        <?php if ($sendOnEnter) { ?>

          <textarea
            class="form-control"
            name="message"
            placeholder="message ..."
            required
            rows="2"></textarea>
        <?php   } else {   ?>

          <div class="input-group">

            <textarea
              class="form-control"
              name="message"
              placeholder="message ..."
              required
              rows="2"></textarea>

            <button class="btn btn-light border" type="submit">
              <i class="bi bi-paper-plane"></i>
            </button>
          </div>
        <?php   }   ?>
      </div>
    </form>
  </div>

  <script type="module">
    const _ = _brayworth_;
    const collapse = $('#<?= $_collapse ?>');
    const form = $('#<?= $_form ?>');
    const msgList = form.find('.js-messages');
    let lastResponse = 0;

    // Shared Worker setup
    const chatId = `${form.find('input[name="local"]').val()}-${form.find('input[name="remote"]').val()}`;
    const worker = new SharedWorker('<?= strings::url($this->route . '/js/chatworker') ?>');
    worker.port.start();

    // Register chat with worker
    worker.port.postMessage({
      type: 'register',
      chatId,
      route: _.url('<?= $this->route ?>', true),
      local: form.find('input[name="local"]').val(),
      remote: form.find('input[name="remote"]').val(),
      version: form.find('input[name="version"]').val()
    });

    // Listen for updates from worker
    worker.port.onmessage = function(e) {
      const d = e.data;
      if (d.chatId !== chatId) return;

      form.find('input[name="version"]').val(d.version);

      let newMsgs = 0;
      $.each(d.data, (i, m) => {
        if (msgList.find(`>div[data-id="${m.id}"]`).length === 0) {
          let cls = 'col-9 py-1 ps-0 d-flex';
          let msgCls = 'py-1 px-2 bg-light border rounded-top rounded-right me-auto overflow-hidden';
          if (form.find('input[name="local"]').val() == m.local) {
            cls = 'offset-3 col-9 py-1 pe-0 d-flex';
            msgCls = 'py-1 px-2 bg-primary border-light text-white rounded-top rounded-left ms-auto overflow-hidden';
          }
          const mCell = $(`<div>${m.message}</div>`).addClass(msgCls);
          const cell = $('<div></div>').addClass(cls).append(mCell);

          $(`<div data-id="${m.id}"
              data-local="${m.local}"
              data-remote="${m.remote}"
              class="row g-2"></div>`)
            .append(cell)
            .appendTo(msgList);

          newMsgs++;
        }
      });

      if (newMsgs > 0) form.trigger('scroll-messages');

      if (Number(d.unseen) > 0) {
        $('#<?= $_unseen ?>').removeClass('d-none').html(d.unseen);
        $('#<?= $_unseen ?>flash').removeClass('d-none');
      } else {
        $('#<?= $_unseen ?>').addClass('d-none');
        $('#<?= $_unseen ?>flash').addClass('d-none');
      }
    };

    // Request update from worker
    function requestUpdate() {
      worker.port.postMessage({
        type: 'update',
        chatId,
        local: form.find('input[name="local"]').val(),
        remote: form.find('input[name="remote"]').val(),
        version: form.find('input[name="version"]').val()
      });
    }

    collapse
      .on('show.bs.collapse', e => setTimeout(() => form.trigger('seen-mark'), 3000))
      .on('shown.bs.collapse', e => form.trigger('scroll-messages'));

    form.find('textarea[name="message"]')
      .on('focus', e => form.trigger('seen-mark'))
      .on('keydown', (e) => {
        <?php if ($sendOnEnter) { ?>
          if (!e.shiftKey && e.keyCode == 13) {
            form.submit();
          }
        <?php } else { ?>
          if (e.ctrlKey && e.keyCode == 13) {
            form.submit();
          }
        <?php } ?>
      });

    $('#<?= $_close ?>').on('click', function(e) {
      e.stopPropagation();
      e.preventDefault();
      $(this).closest('.card').remove();
      worker.port.postMessage({ type: 'unregister', chatId });
    });

    form
      .on('scroll-messages', function(e) {
        const p = msgList.parent();
        if (p.length > 0) p[0].scrollTop = p[0].scrollHeight;
      })
      .on('seen-mark', function(e) {
        const payload = {
          action: 'seen-mark',
          local: this.local.value,
          remote: this.remote.value,
          version: this.version.value
        };
        _.fetch.post(_.url('<?= $this->route ?>'), payload)
          .then(d => ('ack' == d.response) ? requestUpdate() : _growl(d));
      })
      .on('sending-off', e => {
        form.find('div[role="status"] > span').empty();
        form.find('div[role="status"]').addClass('d-none').removeClass('d-flex');
        form.find('textarea[name="message"]').removeClass('d-none').focus();
      })
      .on('sending-on', e => {
        form.find('div[role="status"] > span').text(form.find('textarea[name="message"]').val());
        form.find('div[role="status"]').addClass('d-flex').removeClass('d-none');
        form.find('textarea[name="message"]').addClass('d-none');
      })
      .on('submit', function(e) {
        form.trigger('sending-on');
        _.fetch.post.form(_.url('<?= $this->route ?>'), this)
          .then(d => {
            if ('ack' == d.response) {
              form.find('textarea[name="message"]').val('');
              form.trigger('sending-off');
              requestUpdate();
            } else {
              console.log(d);
            }
          });
        return false;
      });

    // Initial update request
    requestUpdate();

    <?php if ($remote->id) {  ?>
      $('#<?= $_report ?>').on('click', function(e) {
        _.hideContexts(e);
        let url = _.url('<?= $this->route ?>/report/' + this.dataset.id);
        _.get.modal(url);
      });
    <?php } ?>
  </script>
</div>