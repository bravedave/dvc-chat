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

use bravedave\dvc\{push, strings};
?>

<footer class="footer-fixed" id="<?= $_footer = strings::rand() ?>">

  <div class="container-fluid">

    <div class="row mb-0">

      <div class="col-1 position-relative">

        <button class="btn btn-sm btn-light js-chat" type="button">
          <i class="bi bi-chat"></i>
        </button>

        <div class="accordion position-absolute d-none"
          style="bottom: 20px; left: 0;"
          id="<?= $_accordion = strings::rand() ?>"></div>
      </div>

      <div class="col text-end" id="brayworthLOGO">
        <a title="software by BrayWorth using php" href="https://brayworth.com" target="_blank">BrayWorth</a>
      </div>
    </div>
  </div>
  <script type="module">

    const _ = _brayworth_;
    const footer = $('#<?= $_footer ?>');
    const accordion = $('#<?= $_accordion ?>');

    const chatBox = u => {

      // console.log(u);

      if (accordion.find('[data-id="' + u + '"]').length < 1) {

        fetch(_.url('chat/chatbox/' + u + '/<?= users::currentUser() ?>'))
          .then(data => data.text())
          .then(html => {

            const card = $(html);
            card.attr('data-id', u);

            card.find('.collapse').attr('data-parent', '#<?= $_accordion ?>');

            accordion.append(card).removeClass('d-none');
            card.find('.collapse').collapse('show');
          });
      }
    };

    const f = () => {

      if (document.hasFocus()) {

        const payload = {
          action: 'get-unseen',
          local: 0
        };

        _.fetch.post(_.url('chat'), payload).then(d => {

          if ('ack' == d.response) {

            $.each(d.unseen, (i, unseen) => {

              if (Number(unseen.count) > 0) chatBox(unseen.local);
            });

            // console.table( d.unseen);
          }

          setTimeout(f, 10000);
        });

      } else {

        setTimeout(f, 10000);
      }
    };

    setTimeout(f, 1000);

    footer.find('.js-chat').on('click', e => {
      e.stopPropagation();

      const payload = {
        action: 'get-users'
      };

      _.fetch.post(_.url('chat'), payload).then(d => {

        if ('ack' == d.response) {

          _.hideContexts();

          const _context = _.context();

          $.each(d.users, (i, u) => {

            if (<?= users::currentUser() ?> != u.id) {

              const ctrl = $(`<a href="#" data-id="${u.id}">${u.name}</a>`)
                .on('click', function(e) {

                  e.stopPropagation();
                  e.preventDefault();

                  chatBox(this.dataset.id);
                  _context.close();
                });

              const a = new Date(u.access);
              const now = new Date();
              const secs = (now.getTime() - a.getTime()) / 1000;
              if (secs < 60) {

                ctrl.prepend('<i class="bi bi-circle text-success"></i>');
              } else if (secs < 600) {

                ctrl.prepend('<i class="bi bi-circle text-warning"></i>');
              }

              _context.append(ctrl);
            }
          });

          _context.open(e);
        }
      });
    });


    <?php if (class_exists('dvc\push') && push::enabled()) { ?>

      /**
       * Check the current Notification permission.
       * If its denied, skip this until the user
       * changes the permission manually
       */

      if (Notification.permission === 'denied') {
        console.warn('Notifications are denied by the user');

      } else {

        _.push.url = _.url('chat');
        _.push.applicationServerKey = '<?= trim(config::notification_keys()->pubKey) ?>';
        _.push.serviceWorker = _.url('serviceWorker');
        _.push.load();

        $('#<?= $_chat ?>')
          .on('contextmenu', function(e) {
            if (e.shiftKey)
              return;

            e.stopPropagation();
            e.preventDefault();

            _brayworth_.hideContexts();

            let _me = $(this);
            let _context = _brayworth_.context();

            if (_.push.active) {
              _context.append($('<a href="#">Unsubscribe from Notifications</a>').on('click', e => {
                e.stopPropagation();
                e.preventDefault();

                _context.close();
                _me.trigger('unsubscribe');

              }));

              _context.append($('<a href="#">Send test Message</a>').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();

                _context.close();
                _me.trigger('send-test-message');

              }));

            } else {
              _context.append($('<a href="#">Subscribe for Notifications</a>').on('click', e => {
                e.stopPropagation();
                e.preventDefault();

                _context.close();
                _me.trigger('subscribe');

              }));

            }

            _context.open(e);

          })
          .on('send-test-message', e => {
            _.post({
              url: _.url('chat'),
              data: {
                action: 'send-test-message'

              },

            }).then(d => {
              if ('ack' == d.response) {} else {
                _.growl(d);

              }

            });

          })
          .on('subscribe', e => {
            // _.push.unsubscribe() :
            _.push.subscribe();

          })
          .on('unsubscribe', e => {
            // _.push.unsubscribe() :
            _.push.unsubscribe();

          });

      }

    <?php } // if ( push::enabled())
    ?>
  </script>
</footer>