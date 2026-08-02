<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();


?>

<div style="padding-top: 5%;"  class="login-box">
  <div class="card">
    <div class="card-header">
      <h3><?= $_please_login ?></h3>
    </div>
    <div class="card-body">
      <div class="text-center pd-5">
        <img src="img/mikfast.svg" alt="MIKFAST Logo" style="width:84px;height:84px;">
      </div>
      <div  class="text-center">
      <span style="font-size: 25px; margin: 10px;">MIKFAST</span>
      </div>
      <center>
      <form method="post" action="">
      <div class="mm-login-fields" style="width:90%;margin:0 auto;">
        <div class="form-group text-center" style="margin-bottom:12px;">
          <label class="sr-only" for="_username">Username</label>
          <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="text" name="user" id="_username" placeholder="Username" autocomplete="username" required="1" autofocus>
        </div>
        <div class="form-group text-center" style="margin-bottom:12px;">
          <label class="sr-only" for="_password">Password</label>
          <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="password" name="pass" id="_password" placeholder="Password" autocomplete="current-password" required="1">
        </div>
        <div class="form-group text-center">
          <input style="width: 100%; margin-top:8px; height: 35px; font-weight: bold; font-size: 17px;" class="btn-login bg-primary pointer" type="submit" name="login" value="Login">
        </div>
        <div class="text-center">
          <?= $error; ?>
        </div>
      </div>
      </form>
      </center>
    </div>
  </div>
</div>

</body>
</html>
