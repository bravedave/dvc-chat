<?php
/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * styleguide : https://codeguide.co/
 *
 * where I am local it's on right in blue
 * if I am remote it's on left in white
*/

namespace dvc\chat;
use strings;

$remote = $this->data->remote;  ?>

<?php
foreach ($this->data->dtoSet as $dto) {
  $cls = 'col-9 py-1';
  $msgCls = 'py-1 px-2 bg-light border rounded-top rounded-right mr-auto overflow-hidden';
  $msgSubCls = "small text-muted font-italic";

  if ( $dto->local == users::currentuser()) {
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
          <?= strings::asShortDate( $dto->created, $time = true) ?>

        </div>

      </div>

    </div>

  </div>

<?php
}

