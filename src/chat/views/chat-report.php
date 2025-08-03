<?php

/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * where I am local it's on right in blue
 * if I am remote it's on left in white
 */

namespace dvc\chat;

use bravedave\dvc\strings;
use theme; ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-fullscreen-sm-down modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" tabindex="-1"></button>
        </div>

        <div class="modal-body">

          <?php
          foreach ($dtoSet as $dto) {
            $cls = 'col-9 py-1';
            $msgCls = 'py-1 px-2 bg-light border rounded-top rounded-right mr-auto overflow-hidden';
            $msgSubCls = "small text-muted font-italic";

            if ($dto->local == users::currentuser()) {
              $cls = 'offset-3 col-9 py-1';   // it's me
              $msgCls = 'py-1 px-2 bg-primary border-light text-white rounded-top rounded-left ml-auto overflow-hidden';   // it's me
              $msgSubCls = "small text-muted font-italic ml-auto";
            } ?>
            <div class="row">
              <div class="<?= $cls ?>">
                <div class="d-flex">
                  <div class="<?= $msgCls ?>"><?= $dto->message ?></div>
                </div>

                <div class="d-flex">
                  <div class="<?= $msgSubCls ?>">
                    <?= $dto->seen ? 'seen ' : '' ?>
                    <?= strings::asShortDate($dto->created, $time = true) ?>
                  </div>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>

        <div class="modal-footer">
          <div class="js-message"></div>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => {
      const form = $('#<?= $_form ?>');
      const modal = $('#<?= $_modal ?>');

      const msg = txt => {

        const ctl = modal.find('.js-message').html(txt);
        ctl[0].className = 'me-auto js-message small p-2';
        return ctl;
      };

      const alert = txt => msg(txt).addClass('alert alert-warning');

      modal.on('shown.bs.modal', () => {

        form
          .on('submit', function(e) {
            // const _data = $(this).serializeFormJSON();
            _.fetch.post.form(_.url('<?= $this->route ?>'), this)
              .then(d => {
                if ('ack' == d.response) {} else {
                  _.growl(d);
                }
              });

            // console.table( _data);
            return false;
          });
      });
    })(_brayworth_);
  </script>
</form>