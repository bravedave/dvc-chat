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

<form id="<?= $_form = strings::rand() ?>">
    <input type="hidden" name="action" value="post" />
    <input type="hidden" name="sender" value="<?= currentUser::id() ?>" />
    <input type="hidden" name="target" value="0" />

    <div class="card m-1" style="width: 18rem;">
        <div class="card-header"><?= config::label ?></div>
        <div style="max-height: 20em; overflow-y: auto;">
            <ul class="list-group list-group-flush" messages></ul>

        </div>

        <textarea class="form-control" name="message" required rows="3"></textarea>

        <div class="row">
            <div class="col d-flex py-1">
                <button class="btn btn-outline-primary ml-auto" type="submit">Send</button>

            </div>

        </div>

    </div>

</form>
<script>
((_) => {
    let timeout = false;

    $('#<?= $_form ?> textarea[name="message"]').on('keydown', (e) => {
        if (e.ctrlKey && e.keyCode == 13) {
            $('#<?= $_form ?>').submit();

        }

    });

    $('#<?= $_form ?>')
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

        clearTimeout( timeout);

        let _form = $(this);

        _.post({
            url : _.url('<?= $this->route ?>'),
            data : {
                action : 'get'
            },

        }).then( function( d) {
            if ( 'ack' == d.response) {
                let _list = $('[messages]', _form);
                $.each( d.data, (i, m) => {

                    let found = false;
                    $('>li', _list).each( ( i, el) => {
                        let _el = $(el);
                        let _data = _el.data();

                        if ( m.id == _data.id) {
                            found = true;
                            return false;

                        }

                    });

                    if ( !found) {
                        let _m = $('<li class="bg-primary text-white list-group-item"></li>');

                        _m
                        .html( m.message)
                        .data('id', m.id);

                        _list.append( _m);

                    }

                });

                ((_) => {
                    _.scrollTop = _.scrollHeight;

                })(_list.parent()[0]);

                timeout = setTimeout(() => {
                    _form.trigger('update-chat');

                }, <?= config::$DVC_CHAT_REFRESH ?>);

            }

        });

    })
    .trigger('update-chat');

})(_brayworth_);
</script>
