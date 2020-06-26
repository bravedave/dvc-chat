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

?>

<div class="card m-1" style="width: 18rem;">
    <div class="card-header d-flex">
        <?= $this->data->remote->name ?>
        <span class="badge badge-light d-none" id="<?= $_unseen = strings::rand() ?>">&nbsp;</span>
        <button type="button" class="btn btn-light ml-auto" style="margin: -12px -20px -12px 0;"
            data-toggle="collapse"
            data-target="#<?= $_collapse = strings::rand() ?>"><i id="<?= $_collapse ?>icon" class="fa fa-window-minimize"></i></button>

    </div>

    <div class="collapse fade" id="<?= $_collapse ?>">
        <form id="<?= $_form = strings::rand() ?>">
            <input type="hidden" name="action" value="post" />
            <input type="hidden" name="local" value="<?= $this->data->local->id ?>" />
            <input type="hidden" name="remote" value="<?= $this->data->remote->id ?>" />
            <input type="hidden" name="version" value="0" />

            <div class="card-body p-1">
                <div style="max-height: 20em;" class="container-fluid overflow-auto" messages></div>

                <textarea class="form-control" name="message" required rows="3"></textarea>

                <div class="row">
                    <div class="col d-flex py-1">
                        <button class="btn btn-outline-primary ml-auto" type="submit">Send</button>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>
<script>
((_) => {
    let lastResponse = 0;

    $('#<?= $_collapse ?>')
    .on( 'show.bs.collapse', function( e) {
        $('#<?= $_collapse ?>icon').removeClass('fa-window-restore').addClass('fa-window-minimize');
        setTimeout(() => {
            $('#<?= $_form ?>').trigger( 'seen-mark');

        }, 3000);;


    })
    .on( 'shown.bs.collapse', function( e) {
        ((_) => {
            _.scrollTop = _.scrollHeight;

        })($('#<?= $_form ?> [messages]'));



    })
    .on( 'hide.bs.collapse', function( e) {
        $('#<?= $_collapse ?>icon').removeClass('fa-window-minimize').addClass('fa-window-restore');


    });

    $('#<?= $_form ?> textarea[name="message"]').on('keydown', (e) => {
        if (e.ctrlKey && e.keyCode == 13) {
            $('#<?= $_form ?>').submit();

        }

    });

    $('#<?= $_form ?>')
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
                version : _data.version,

            },

        }).then( function( d) {
            if ( 'ack' == d.response) {
                _form.trigger('update-chat');

            }

        });

    })
    .on( 'submit', function(e) {
        let _form = $(this);
        let _data = _form.serializeFormJSON();

        // console.log( _data);

        _.post({
            url : _.url('<?= $this->route ?>'),
            data : _data,

        }).then( function( d) {
            // console.log( d);
            if ( 'ack' == d.response) {
                $('textarea[name="message"]', _form).val('');
                _form.trigger('update-chat');

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

                        if ( m.id == _data.id) {
                            found = true;
                            return false;

                        }

                    });

                    if ( !found) {
                        let cls = 'col-9 py-1 pl-0 d-flex';
                        let msgCls = 'py-1 px-2 bg-light border rounded-top rounded-right mr-auto';
                        if ( _data.local == m.local) {
                            cls = 'offset-3 col-9 py-1 pr-0 d-flex';   // it's me
                            msgCls = 'py-1 px-2 bg-primary border-light text-white rounded-top rounded-left ml-auto';   // it's me

                        }

                        let mCell = $('<div></div>').addClass( msgCls).html( m.message);

                        let cell = $('<div></div>').addClass(cls).append( mCell);

                        $('<div class="row"></div>').data('id', m.id).append( cell).appendTo(msgList);

                        newMsgs ++;

                    }

                });

                ((_) => {
                    _.scrollTop = _.scrollHeight;

                })(msgList[0]);

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

                if ( Number( d.unseen) > 0 ) {
                    $('#<?= $_unseen ?>').removeClass( 'd-none').html(d.unseen);

                }
                else {
                    $('#<?= $_unseen ?>').addClass( 'd-none');

                }
                // console.log('update-chat');


                timeout = setTimeout(() => {
                    _form.trigger('update-chat');

                }, nextCheck);
                _form.data('timeout', timeout);

            }

        });

    })
    .trigger('update-chat');

})(_brayworth_);
</script>
