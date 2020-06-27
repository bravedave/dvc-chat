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
*/	?>
<footer class="footer">
	<div class="container-fluid">
		<div class="row mb-0">
			<div class="col-1">
				<button class="btn btn-sm btn-light" type="button"
                    id="<?= $_chat = strings::rand() ?>">
					<?= dvc\icon::get( dvc\icon::chat ) ?>

				</button>

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
        let chat = $('<div class="position-absolute" style="bottom: 20px; left: 0;"></div>');
        chat.load( _.url('/chatbox/'+u+'/0'));
        $( 'footer').append( chat);

    };

    let f = () => {
        _.post({
            url : _.url('/'),
            data : {
                action : 'get-unseen',
                local : 0,
                remote : 1

            },

        }).then( function( d) {
            if ( 'ack' == d.response) {
                if ( Number(d.unseen) > 0) {
                    chatBox(1);

                }
                else {
                    setTimeout(f, 5000);

                }

            }

        });

    };

    setTimeout(f, 1000);

    $('#<?= $_chat ?>').on( 'click', function( e) {
        e.stopPropagation();

        let _me = $(this);

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
