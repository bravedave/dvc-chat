<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * DO NOT change this file
 * Copy it to <application>/app/views/ and modify it there
 *
*/  ?>

<footer class="footer">
	<div class="container-fluid">
		<div class="row mb-0">
			<div class="col-1 position-relative">
				<button class="btn btn-sm btn-light" type="button"
                    id="<?= $_chat = strings::rand() ?>">
					<?= dvc\icon::get( dvc\icon::chat ) ?>

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
((_) => {
    let chatBox = ( u ) => {
        if ( $( '[data-id="' + u + '"]', '#<?= $_accordion ?>').length < 1) {
            fetch( _.url('<?= $this->route ?>/chatbox/'+u+'/<?= dvc\chat\users::currentUser() ?>'))
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
                url : _.url('/'),
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

                setTimeout(f, 5000);

            });

        }
        else {
            setTimeout(f, 5000);

        }

    };

    setTimeout(f, 1000);

    $('#<?= $_chat ?>').on( 'click', function( e) {
        e.stopPropagation();

        let _me = $(this);
        // console.log( _me);

        _.post({
            url : _.url('<?= $this->route ?>'),
            data : {
                action : 'get-users'

            },

        }).then( function( d) {
            if ( 'ack' == d.response) {

                _.hideContexts();

                let _context = _.context();

                $.each( d.users, (i, u) => {
                    if ( <?= currentUser::id() ?> != u.id) {
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

                        _context.append( ctrl);

                    }

                });

                _context.open( e);

            }

        });

    });

})(_brayworth_);
</script>
