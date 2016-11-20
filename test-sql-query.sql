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

