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

use dvc\icon;
use dvc\push;
use strings;

?>

<footer class="footer-fixed">
	<div class="container-fluid">
		<div class="row mb-0">
			<div class="col-1 position-relative">
				<button class="btn btn-sm btn-light" type="button"
          id="<?= $_chat = strings::rand() ?>">
					<?= icon::get( icon::chat ) ?>

				</button>

        <div class="accordion position-absolute d-none"
          style="bottom: 20px; left: 0;"
          id="<?= $_accordion = strings::rand() ?>"></div>

			</div>

			<div class="col text-right" id="brayworthLOGO">
				<a title="software by BrayWorth using php" href="https://brayworth.com" target="_blank">BrayWorth</a>

			</div>

		</div>

	</div>

</footer>
<script>
$(document).ready( () => {
  (_ => {
    let chatBox = ( u ) => {
      if ( $( '[data-id="' + u + '"]', '#<?= $_accordion ?>').length < 1) {
        fetch( _.url('chat/chatbox/'+u+'/<?= users::currentUser() ?>'))
        .then( data => data.text())
        .then( html => {
          let card = $(html)

          card.attr('data-id', u);

          $('.collapse', card).attr('data-parent', '#<?= $_accordion ?>');

          $('#<?= $_accordion ?>').append(card).removeClass('d-none');

        });

      }

    };

    let f = () => {
      if ( document.hasFocus()) {
        _.post({
          url : _.url('chat'),
          data : {
            action : 'get-unseen',
            local : 0

          },

        }).then( function( d) {
          if ( 'ack' == d.response) {
            $.each( d.unseen, ( i, unseen) => {
              if ( Number(unseen.count) > 0) {
                chatBox(unseen.local);

              }

            });
            // console.table( d.unseen);

          }
          setTimeout(f, 15000);

        });

      }
      else {
          setTimeout(f, 15000);

      }

    };

    setTimeout(f, 1000);

    $('#<?= $_chat ?>')
    .on( 'click', function( e) {
        e.stopPropagation();

        let _me = $(this);
        // console.log( _me);

        _.post({
          url : _.url('chat'),
          data : { action : 'get-users'},

        }).then( function( d) {
          // console.log( _me);
          if ( 'ack' == d.response) {

            _.hideContexts();

            let _context = _.context();

            $.each( d.users, (i, u) => {
              if ( <?= users::currentUser() ?> != u.id) {
                let ctrl = $('<a href="#"></a>');

                ctrl
                .html( u.name)
                .data('id', u.id)
                .on( 'click', function( e) {
                    e.stopPropagation();e.preventDefault();

                    let _me = $(this);
                    let _data = _me.data();

                    chatBox( _data.id);
                    _context.close();

                });

                let a = new Date( u.access);
                let now = new Date();
                let secs = ( now.getTime() - a.getTime()) / 1000;
                if ( secs < 60) {
                    ctrl.prepend( '<i class="fa fa-circle text-success"></i>');

                }
                else if ( secs < 600) {
                    ctrl.prepend( '<i class="fa fa-circle text-warning"></i>');

                }

                _context.append( ctrl);

            }

          });

          _context.open( e);

        }

      });

    });

    <?php if ( class_exists( 'dvc\push') && push::enabled()) { ?>

      /**
      * Check the current Notification permission.
      * If its denied, skip this until the user
      * changes the permission manually
      */

      if (Notification.permission === 'denied') {
        console.warn('Notifications are denied by the user');

      }
      else {

        _.push.url = _.url( 'chat');
        _.push.applicationServerKey = '<?= trim( config::notification_keys()->pubKey) ?>';
        _.push.serviceWorker = _.url( 'serviceWorker');
        _.push.load();

        $('#<?= $_chat ?>')
        .on( 'contextmenu', function( e) {
          if ( e.shiftKey)
            return;

          e.stopPropagation();e.preventDefault();

          _brayworth_.hideContexts();

          let _me = $(this);
          let _context = _brayworth_.context();

          if ( _.push.active) {
            _context.append( $('<a href="#">Unsubscribe from Notifications</a>').on( 'click', e => {
              e.stopPropagation();e.preventDefault();

              _context.close();
              _me.trigger( 'unsubscribe');

            }));

            _context.append( $('<a href="#">Send test Message</a>').on( 'click', function( e) {
              e.stopPropagation();e.preventDefault();

              _context.close();
              _me.trigger( 'send-test-message');

            }));

          }
          else {
            _context.append( $('<a href="#">Subscribe for Notifications</a>').on( 'click', e => {
              e.stopPropagation();e.preventDefault();

              _context.close();
              _me.trigger( 'subscribe');

            }));

          }

          _context.open( e);

        })
        .on( 'send-test-message', e => {
          _.post({
            url : _.url('chat'),
            data : {
              action : 'send-test-message'

            },

          }).then( d => {
            if ( 'ack' == d.response) {
            }
            else {
              _.growl( d);

            }

          });

        })
        .on( 'subscribe', e => {
          // _.push.unsubscribe() :
          _.push.subscribe();

        })
        .on( 'unsubscribe', e => {
          // _.push.unsubscribe() :
          _.push.unsubscribe();

        });

      }

    <?php } // if ( push::enabled()) ?>

  })(_brayworth_);

});
</script>
