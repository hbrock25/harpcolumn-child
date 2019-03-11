SELECT SQL_CALC_FOUND_ROWS u.user_email, mu.startdate as startdate, mu.enddate as enddate, mu.status, mu.membership_id, mu2.status, mu2.membership_id
       FROM wp_users u, wp_pmpro_memberships_users mu2
       LEFT JOIN wp_pmpro_memberships_users mu ON u.ID = mu.user_id
       AND mu.status = 'inactive' AND mu.membership_id IN (2, 6)
       AND date(mu.enddate) < CURDATE() AND date(mu.enddate) > (DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
       WHERE u.ID = mu2.user_id AND mu2.membership_id = 1 AND mu2.status = 'active'
       GROUP BY u.ID
       ORDER BY u.user_registered DESC;


LIMIT 0, 15;

SELECT SQL_CALC_FOUND_ROWS u.user_email, mu2.status, mu2.enddate, mu2.status, mu.status, mu.membership_id
       FROM wp_users u
       INNER JOIN wp_pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'expired'
       INNER JOIN wp_pmpro_memberships_users mu ON u.ID = mu.user_id
       AND mu.status = 'active' AND mu.membership_id = 1
       WHERE mu2.membership_id IN (2, 6)
       AND date(mu2.enddate) < CURDATE() AND date(mu2.enddate) > (DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
       ORDER BY u.user_registered DESC;


LIMIT 0, 15;

SELECT SQL_CALC_FOUND_ROWS u.user_email, mu2.status, mu2.enddate, mu2.membership_id
       FROM wp_users u
       INNER JOIN wp_pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'expired'
       WHERE mu2.membership_id IN (2, 6)
       AND date(mu2.enddate) < CURDATE() AND date(mu2.enddate) > (DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
       ORDER BY u.user_registered DESC;

-- the below pulls every user that has a value in shipping_first_name and then joins the ě@##$ skinny user_meta table
-- once for each of the rest of the shipping address fields. Phabulous.


SELECT
	u.user_email,
	sfn.meta_value as shipping_first_name,
	 sln.meta_value as shipping_last_name,
	 sco.meta_value as shipping_company,
	 sa1.meta_value as shipping_address_1,
	 sa2.meta_value as shipping_address_2,
	 sac.meta_value as shipping_city,
	 sas.meta_value as shipping_state,
	 sap.meta_value as shipping_postcode
	 from wp_users u
	 inner join wp_usermeta sfn on u.id = sfn.user_id and sfn.meta_key = 'shipping_first_name'
	 left outer join wp_usermeta sln on u.id = sln.user_id and sln.meta_key = 'shipping_last_name'
	 left outer join wp_usermeta sco on u.id = sco.user_id and sco.meta_key = 'shipping_company'
	 left outer join wp_usermeta sa1 on u.id = sa1.user_id and sa1.meta_key = 'shipping_address_1'
	 left outer join wp_usermeta sa2 on u.id = sa2.user_id and sa2.meta_key = 'shipping_address_2'
	 left outer join wp_usermeta sac on u.id = sac.user_id and sac.meta_key = 'shipping_city'
	 left outer join wp_usermeta sas on u.id = sas.user_id and sas.meta_key = 'shipping_state'
	 left outer join wp_usermeta sap on u.id = sap.user_id and sap.meta_key = 'shipping_postcode'
	 left outer join wp_usermeta scn on u.id = scn.user_id and scn.meta_key = 'shipping_country'
	 where scn.meta_value = 'US'
INTO OUTFILE 'addresses.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
