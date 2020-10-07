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
use currentUser;
use strings;

$sendOnEnter = true;
// $sendOnEnter = false;

?>

<div class="card m-1" style="width: 18rem;">
  <div class="card-header p-0 d-flex">
    <button type="button" class="btn btn-light flex-fill text-left"
      data-toggle="collapse"
      data-target="#<?= $_collapse = strings::rand() ?>">
      <?php
      if ( 0 == $this->data->remote->id) {
        printf('%s => %s',
          $this->data->local->name,
          $this->data->remote->name);

      }
      else {
        print $this->data->remote->name;

      }   ?>
      <span class="badge badge-light d-none" id="<?= $_unseen = strings::rand() ?>">&nbsp;</span>
      <span class="spinner-grow spinner-grow-sm text-danger d-none" id="<?= $_unseen ?>flash">&nbsp;</span>

    </button>

    <?php if ( $this->data->remote->id) {  ?>
      <button type="button" class="btn btn-light"
        data-id="<?= $this->data->remote->id ?>"
        id="<?= $_report = strings::rand() ?>">
        <i class="fa fa-external-link"></i>

      </button>

    <?php } ?>

    <button type="button" class="btn btn-light"
      data-role="close"
      id="<?= $_close = strings::rand() ?>">
      <i class="fa fa-times"></i>

    </button>

  </div>

  <div class="collapse fade" id="<?= $_collapse ?>">
    <form id="<?= $_form = strings::rand() ?>">
        <input type="hidden" name="action" value="post" />
        <input type="hidden" name="remote" value="<?= $this->data->remote->id ?>" />
        <input type="hidden" name="local" value="<?= $this->data->local->id ?>" />
        <input type="hidden" name="version" value="0" />

        <div class="card-body p-1 overflow-auto" style="height: 65vh">
            <div class="container-fluid" messages></div>

        </div>

        <div class="card-footer p-1">
          <div class="p-2 d-none position-relative" role="status">
            <span class="position-absolute text-muted"></span>
            <div class="spinner-border mx-auto my-2">
              <span class="sr-only">Loading...</span>

            </div>

          </div>

          <?php   if ( $sendOnEnter) { ?>
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
                <div class="input-group-append">
                    <button class="btn btn-light border" type="submit">
                        <i class="fa fa-paper-plane-o"></i>

                    </button>

                </div>

            </div>

          <?php   }   ?>

        </div>

    </form>

  </div>

  <script>
    ((_) => {
      let lastResponse = 0;

      $('#<?= $_collapse ?>')
      .on( 'show.bs.collapse', function( e) {
          setTimeout(() => {
              $('#<?= $_form ?>').trigger( 'seen-mark');

          }, 3000);;


      })
      .on( 'shown.bs.collapse', function( e) {
          $('#<?= $_form ?>').trigger('scroll-messages');

      });

      $('#<?= $_form ?> textarea[name="message"]')
      .on('focus', (e) => {
          $('#<?= $_form ?>').trigger( 'seen-mark');

      })
      .on('keydown', (e) => {
        <?php
        if ( $sendOnEnter) { ?>
          if (!e.shiftKey && e.keyCode == 13) {
            $('#<?= $_form ?>').submit();

          }
        <?php
          } else { ?>
          if (e.ctrlKey && e.keyCode == 13) {
            $('#<?= $_form ?>').submit();

          }
        <?php
        } ?>

      });

      $('#<?= $_close ?>').on( 'click', function( e) {
          e.stopPropagation();e.preventDefault();

          let _me = $(this);
          _me.closest('.card').remove();

      });

      $('#<?= $_form ?>')
      .on( 'scroll-messages', function(e) {
          ((_) => {
              if (_.length > 0) {
                  _[0].scrollTop = _[0].scrollHeight;

              }

          })($('[messages]', this).parent());

      })
      .on( 'seen-mark', function(e) {
          let _form = $(this);

          let timeout = _form.data('timeout');
          if ( Number(timeout) > 0) clearTimeout( timeout);

          let _data = _form.serializeFormJSON();

          // console.log( _data);

          _.post({
              url : _.url('<?= $this->route ?>'),
              data : {
                  action : 'seen-mark',
                  local : _data.local,
                  remote : _data.remote,
                  version : _data.version,

              },

          }).then( function( d) {
              if ( 'ack' == d.response) {
                  _form.trigger('update-chat');

              }

          });

      })
      .on( 'sending-off', function( e) {
        let _form = $(this);

        $('div[role="status"] > span', _form).text( '');
        $('div[role="status"]', _form).addClass('d-none').removeClass('d-flex');
        $('textarea[name="message"]', _form).removeClass('d-none').focus();

      })
      .on( 'sending-on', function( e) {
        let _form = $(this);

        $('div[role="status"] > span', _form).text( $('textarea[name="message"]', _form).val());
        $('div[role="status"]', _form).addClass('d-flex').removeClass('d-none');
        $('textarea[name="message"]', _form).addClass('d-none');

      })
      .on( 'submit', function(e) {
        let _form = $(this);
        let _data = _form.serializeFormJSON();

        // console.log( _data);

        _form.trigger('sending-on');

        _.post({
          url : _.url('<?= $this->route ?>'),
          data : _data,

        }).then( function( d) {
          if ( 'ack' == d.response) {
            $('textarea[name="message"]', _form).val('');
            _form.trigger('sending-off');
            _form.trigger('update-chat');

          }
          else {
            console.log( d);

          }

        });

        return false;

      })
      .on( 'update-chat', function(e) {

        let _form = $(this);
        let timeout = _form.data('timeout');
        if ( Number(timeout) > 0) clearTimeout( timeout);

        let _data = _form.serializeFormJSON();
        // console.log( _data);

        _.post({
          url : _.url('<?= $this->route ?>'),
          data : {
            action : 'get',
            local : _data.local,
            remote : _data.remote,
            version : _data.version,

          },

        }).then( function( d) {
          if ( 'ack' == d.response) {

            $('input[name="version"]', _form).val( d.version);

            let newMsgs = 0;
            let msgList = $('[messages]', _form);
            $.each( d.data, (i, m) => {

                let found = false;
                $('>div', msgList).each( ( i, el) => {
                    let _el = $(el);
                    let _data = _el.data();

                    // console.log( m);
                    if ( m.id == _data.id) {
                        found = true;
                        return false;

                    }

                });

                if ( !found) {
                    let cls = 'col-9 py-1 pl-0 d-flex';
                    let msgCls = 'py-1 px-2 bg-light border rounded-top rounded-right mr-auto overflow-hidden';
                    if ( _data.local == m.local) {
                        cls = 'offset-3 col-9 py-1 pr-0 d-flex';   // it's me
                        msgCls = 'py-1 px-2 bg-primary border-light text-white rounded-top rounded-left ml-auto overflow-hidden';   // it's me

                    }

                    let mCell = $('<div></div>').addClass( msgCls).html( m.message);

                    let cell = $('<div></div>').addClass(cls).append( mCell);

                    $('<div class="row"></div>').data('id', m.id).append( cell).appendTo(msgList);

                    newMsgs ++;

                }

            });

            if ( newMsgs > 0) _form.trigger('scroll-messages');

            let dt = new Date();
            let tm = Math.round( dt.getTime()/1000);
            if ( newMsgs > 0) lastResponse = tm;

            let nextCheck = 1000;
            if ( tm - lastResponse > 60) {
              nextCheck = 15000;

            }
            else if ( tm - lastResponse > 20) {
              nextCheck = 6000;

            }
            else if ( tm - lastResponse > 10) {
              nextCheck = 4000;

            }
            else if ( tm - lastResponse > 5) {
              nextCheck = 2000;

            }

            // console.log( d.unseen);
            if ( Number( d.unseen) > 0 ) {
              $('#<?= $_unseen ?>').removeClass( 'd-none').html(d.unseen);
              $('#<?= $_unseen ?>flash').removeClass( 'd-none');

            }
            else {
              $('#<?= $_unseen ?>').addClass( 'd-none');
              $('#<?= $_unseen ?>flash').addClass( 'd-none');

            }
            // console.log('update-chat');

            timeout = setTimeout(() => _form.trigger('update-chat'), nextCheck);
            _form.data('timeout', timeout);

          }

        });

      })
      .trigger('update-chat');

      <?php if ( $this->data->remote->id) {  ?>
        $('#<?= $_report ?>').on( 'click', function( e) {
          e.stopPropagation();e.preventDefault();

          let _me = $(this);
          let _data = _me.data();

          let url = _.url( '<?= $this->route ?>/report/' + _data.id);
          _.get.modal( url);

        });

    <?php } ?>

    })(_brayworth_);
  </script>

</div>
