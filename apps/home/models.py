from django.db import models


class JaGAnalyticsData(models.Model):
    project_management_id = models.IntegerField(blank=True, null=True)
    date = models.DateField(blank=True, null=True)
    ecommerce_users = models.FloatField(blank=True, null=True)
    total_revenue = models.FloatField(blank=True, null=True)
    conversion_rate = models.FloatField(blank=True, null=True)
    total_transactions = models.IntegerField(blank=True, null=True)
    avg_order_value = models.FloatField(blank=True, null=True)
    total_ads_clicks = models.IntegerField(blank=True, null=True)
    total_ads_cost = models.FloatField(blank=True, null=True)
    ads_cpc_value = models.FloatField(blank=True, null=True)
    mcf_conversion = models.FloatField(blank=True, null=True)
    mcf_conversion_value = models.FloatField(blank=True, null=True)
    mcf_assisted_conversion = models.FloatField(blank=True, null=True)
    mcf_assisted_value = models.FloatField(blank=True, null=True)
    social_facebook_clicks = models.IntegerField(blank=True, null=True)
    social_twitter_clicks = models.IntegerField(blank=True, null=True)
    social_pinterest_clicks = models.IntegerField(blank=True, null=True)
    social_instagram_clicks = models.IntegerField(blank=True, null=True)
    new_visitors = models.IntegerField(blank=True, null=True)
    returning_visitors = models.IntegerField(blank=True, null=True)
    total_users = models.IntegerField(blank=True, null=True)
    total_pageviews = models.IntegerField(blank=True, null=True)
    bounce_rate = models.FloatField(blank=True, null=True)
    total_sessions = models.IntegerField(blank=True, null=True)
    session_duration = models.IntegerField(blank=True, null=True)
    session_by_desktop = models.FloatField(blank=True, null=True)
    session_by_tablet = models.FloatField(blank=True, null=True)
    session_by_mobile = models.FloatField(blank=True, null=True)
    total_mobile_users = models.IntegerField(blank=True, null=True)
    total_desktop_users = models.IntegerField(blank=True, null=True)
    total_tablet_users = models.IntegerField(blank=True, null=True)
    total_paid_pageviews = models.IntegerField(blank=True, null=True)
    total_paid_users = models.IntegerField(blank=True, null=True)
    total_referral_pageviews = models.IntegerField(blank=True, null=True)
    total_referral_users = models.IntegerField(blank=True, null=True)
    total_organic_pageviews = models.IntegerField(blank=True, null=True)
    total_organic_users = models.IntegerField(blank=True, null=True)
    total_direct_pageviews = models.IntegerField(blank=True, null=True)
    total_direct_users = models.IntegerField(blank=True, null=True)
    total_email_pageviews = models.IntegerField(blank=True, null=True)
    total_email_users = models.IntegerField(blank=True, null=True)
    fb_clicks = models.IntegerField(blank=True, null=True)
    fb_cpc = models.FloatField(blank=True, null=True)
    fb_impressions = models.IntegerField(blank=True, null=True)
    fb_reach = models.IntegerField(blank=True, null=True)
    fb_action_type_like = models.IntegerField(blank=True, null=True)
    fb_action_type_link_click = models.IntegerField(blank=True, null=True)
    fb_spend = models.FloatField(blank=True, null=True)
    fb_mobile_app_purchase_roas = models.FloatField(blank=True, null=True)
    fb_purchase_roas = models.FloatField(blank=True, null=True)
    fb_website_purchase_roas = models.FloatField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'ja_g_analytics_data'


class JaConversionValue(models.Model):
    cs_id = models.IntegerField()
    project_management_id = models.IntegerField()
    date = models.DateField()
    total_conversion = models.IntegerField()
    conversion_value = models.FloatField()

    class Meta:
        managed = False
        db_table = 'ja_conversion_value'

class JaConversionSource(models.Model):
    source = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'ja_conversion_source'