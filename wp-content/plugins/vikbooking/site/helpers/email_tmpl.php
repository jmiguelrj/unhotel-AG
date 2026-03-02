<center class="text-direction-{lang_direction}" style="background: #fdfdfd; padding: 40px 0; color: #666; width: 100%; table-layout: fixed; direction: {lang_direction};">
	<div style="text-align: center;">
			<p>{logo}</p>
	</div>
	<div style="max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; box-sizing: border-box; border-radius: 6px; ">
		<h1 style="font-size: 32px; font-weight: 500; color: rgb(31, 32, 32); margin: 0px 0px 20px; padding: 0px;">{company_name}</h1>
		<!--[if (gte mso 9)|(IE)]>
			<table width="800" align="center">
			<tr>
			<td>
			<![endif]-->
		<table style="margin: 0 auto; width: 100%; max-width: 800px; border-spacing: 0; font-family: sans-serif;">
			<tbody>
				<tr>
					<td style="padding:0;">
						<!--[if (gte mso 9)|(IE)]>
						<table width="100%">
						<tr>
						<td width="50%" valign="top">
						<![endif]-->
						<div style="width: 100%; max-width: 355px; display: inline-block; vertical-align: top; text-align: {text_natural_direction};">
							<table width="90%" style="margin: 10px auto 0; padding: 5px; font-size: 14px; background:#f2f3f7;border-radius: 30px;">
								<tr>
									<td style="padding: 10px; line-height: 1.4em;">
										<div style="min-height: 270px;">
											<h3 style="background: rgb(250, 70, 118); display: inline-block; padding: 5px 10px; text-transform: uppercase; font-size: 16px; color: rgb(255, 255, 255);">Sua Reserva</h3>
											<div>
												<p><span>#:</span> <span>{confirmnumb}</span></p>
											</div>
											{confirmnumb_delimiter}
											<div>
												<p><span>N° da Reserva:</span> <span>{order_id}</span></p>
											</div>
											{/confirmnumb_delimiter}
											<div>
												<p><span>Status:</span> <span class="{order_status_class}">{order_status}</span></p>
											</div>
											<div>
												<p><span>Data da reserva:</span> <span>{order_date}</span></p>
											</div>
										</div>
									</td>
								</tr>
							</table>
						</div>
						<!--[if (gte mso 9)|(IE)]>
						</td><td width="50%" valign="top">
						<![endif]-->
						<div style="width: 100%; max-width: 355px; display: inline-block; vertical-align: top; text-align: {text_natural_direction};">
							<table width="90%" style="margin: 10px auto 0; padding: 5px; font-size: 14px; background:#f2f3f7;border-radius: 30px;">
								<tr>
									<td style="padding: 10px; line-height: 1.4em;">
										<div style="min-height: 270px;">
											<h3 style="background: rgb(250, 70, 118); display: inline-block; padding: 5px 10px; text-transform: uppercase; font-size: 16px; color: rgb(255, 255, 255);">Dados Pessoais</h3>
											<p>{customer_info}</p>
										</div>
									</td>
								</tr>
							</table>
						</div>
						<!--[if (gte mso 9)|(IE)]>
						</td>
						</tr>
						</table>
						<![endif]-->
					</td>
				</tr>
				<tr>
					<td style="padding:0;">
						<!--[if (gte mso 9)|(IE)]>
						<table width="100%">
						<tr>
						<td width="50%" valign="top">
						<![endif]-->
						<div style="width: 100%; max-width: 355px; display: inline-block; vertical-align: top; text-align: {text_natural_direction};">
							<table width="90%" style="background:#f2f3f7; margin: 10px auto 0; padding: 5px; font-size: 14px; border-radius: 30px;">
								<tr>
									<td style="padding: 10px; line-height: 1.4em;">
										<div>
											<div><strong>Apartamento</strong><span> {rooms_count}</span></div>
											<div>
												{rooms_info}
																								{roomfeature VBODEFAULTDISTFEATUREONE}
																							</div>
										</div>
									</td>
								</tr>
							</table>
						</div>
						<!--[if (gte mso 9)|(IE)]>
						</td><td width="50%" valign="top">
						<![endif]-->
						<div style="width: 100%; max-width: 355px; display: inline-block; vertical-align: top; text-align: {text_natural_direction};">
							<table width="90%" style="margin: 10px auto 0; padding: 5px; font-size: 14px; background:#f2f3f7; border-radius: 30px;">
								<tr>
									<td style="padding: 10px; line-height: 1.4em;">
										<div>
											<p>
												<span style="font-weight:600;">Check-in:</span>
												<span>{checkin_date}</span>
											</p>
										</div>
										<div>
											<p>
												<span style="font-weight:600;">Checkout:</span>
												<span>{checkout_date}</span>
											</p>
										</div>
									</td>
								</tr>
							</table>
						</div>
						<!--[if (gte mso 9)|(IE)]>
						</td>
						</tr>
						</table>
						<![endif]-->
					</td>
				</tr>
				<tr>
					<td style="padding: 0; text-align: center;">
						<table width="95%" style="border-spacing: 0; margin: 10px auto 0; padding: 15px; font-size: 14px; background: #fff;">
							<tr>
								<td style="padding: 10px; line-height: 1.4em; text-align: {text_natural_direction};">
									<div>
										<h3 style="background: rgb(250, 70, 118); display: inline-block; padding: 5px 10px; text-transform: uppercase; font-size: 16px; color: rgb(255, 255, 255);">Detalhes da Reserva</h3>
										<div style="padding:10px; margin:2px 0;">
											<div>
												{order_details}
											</div>
											<div style="padding: 10px; background: rgb(242, 243, 247); border: 1px solid rgb(250, 70, 118); margin: 10px 0px;">
												<span>Total</span>
												<span style="float: {text_opposite_direction};">
													<strong>{order_total}</strong>
												</span>
											</div>
											<div>{order_deposit}</div>
											<div>{order_total_paid}</div>
										</div>
									</div>	
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="padding: 0; text-align: center;">
						<table width="95%" style="border-spacing: 0; margin: 0 auto; font-size: 14px; background: #fff;">
							<tr>
								<td style="line-height: 1.4em; text-align: {text_natural_direction};">
									<div>
										<strong>Para visualizar os detalhes da sua reserva, acesse a página abaixo.</strong><br>
										{order_link}
									</div>
									<!-- Reservation Policy (PT + EN) -->
<div style="margin-top:16px; padding:12px; background:#f2f3f7; border:1px solid #e2e3e7; border-radius:6px;">
  <p style="margin:0 0 10px; color:#1f2020; font-weight:600;">Política de Reserva</p>
  <p style="margin:0 0 12px;">
    Ao concluir seu pagamento e não cancelar sua reserva dentro de 24 horas da confirmação, ou 3 horas antes do check-in para reservas no mesmo dia, você confirma que leu, entendeu e concorda com todos os termos descritos na 
    <a href="https://unhotel.com.br/politica-da-reserva/" target="_blank" style="color:#fa4676; text-decoration:underline;">Política de reserva.</a>.
  </p>
  <p style="margin:0;">
    <a href="https://unhotel.com.br/politica-da-reserva/" target="_blank"
       style="display:inline-block; padding:10px 14px; background:#fa4676; color:#ffffff; text-decoration:none; border-radius:4px;">
      Ver Política de Reserva
    </a>
  </p>
  <hr style="border:none; border-top:1px solid #e2e3e7; margin:14px 0;">
  <p style="margin:0 0 10px; color:#1f2020; font-weight:600;">Reservation Policy</p>
  <p style="margin:0 0 12px;">
    By completing your payment and not cancelling your reservation within 24 hours of confirmation or 3 hours before check-in for same day reservations, you confirm that you have read, understood, and agree to all terms outlined in the 
    <a href="https://unhotel.com.br/politica-da-reserva/" target="_blank" style="color:#fa4676; text-decoration:underline;">Reservation policy.</a>.
  </p>
    <p style="margin:0;">
    <a href="https://unhotel.com.br/politica-da-reserva/" target="_blank"
       style="display:inline-block; padding:10px 14px; background:#fa4676; color:#ffffff; text-decoration:none; border-radius:4px;">
      View Reservation Policy
    </a>
  </p>
</div>
									<div>
										<div>{footer_emailtext}</div>
									</div>
									<div>
										<br>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<!--[if (gte mso 9)|(IE)]>
		</td>
		</tr>
		</table>
		<![endif]-->
	</div>
</center><style type="text/css">
<!--
.confirmed {color: #009900;}
.standby {color: #cc9a04;}
.cancelled {color: #ff0000;}
.text-direction-ltr .service-amount {float: right;}
.text-direction-rtl .service-amount {display: inline-block; margin-right: 5px;}
-->
</style>
