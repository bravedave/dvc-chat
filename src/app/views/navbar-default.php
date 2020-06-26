<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 *	DO NOT change this file
 *	Copy it to <application>/app/views/ and modify it there
**/	?>
<nav class="navbar navbar-expand navbar-light bg-light sticky-top" role="navigation" >
	<div class="navbar-brand" ><?= $this->data->title	?></div>

	<ul class="ml-auto navbar-nav">
		<li class="nav-item">
			<a class="nav-link" href="<?= strings::url() ?>">
				<?= dvc\icon::get( dvc\icon::house ) ?>

			</a>

		</li>

		<li class="nav-item">
			<a class="nav-link" href="#" id="<?= $_chat = strings::rand() ?>">
				<?= dvc\icon::get( dvc\icon::chat ) ?>

			</a>

		</li>

		<li class="nav-item">
			<a class="nav-link" href="https://github.com/bravedave/">
				<?= dvc\icon::get( dvc\icon::github ) ?>

			</a>

		</li>

	</ul>

</nav>
<script>
$(document).ready( () => {
    ((_) => {
        let checked = 0;
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
                        let chat = $('<div class="position-absolute" style="bottom: 0; left: 0;"></div>').load( _.url('/chatbox/1/0'))
                        $( 'footer').append( chat);

                    }
                    else {
                        setTimeout(f, 5000);

                    }

                }

            });

        };

        setTimeout(f, 1000);

    })(_brayworth_);

    $('#<?= $_chat ?>').on( 'click', function( e) {
		((_) => {
			let chat = $('<div class="position-absolute" style="bottom: 0; right: 0;"></div>').load( _.url('/chatbox/0/1'))
			$( 'footer').append( chat);

		})(_brayworth_);

		$(this).remove();

	})

});
</script>
