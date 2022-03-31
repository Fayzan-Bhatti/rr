from django import template
from django.contrib.auth.decorators import login_required
from django.http import HttpResponse, HttpResponseRedirect
from django.template import loader
from django.shortcuts import render
from django.urls import reverse
from .models import JaGAnalyticsData
import json
import requests
from .models import JaConversionValue

import datetime
from django.db.models import Sum


@login_required(login_url="/login/")
def index(request):
    #date = datetime.datetime.now()
	date = '2020-08-25'  #hardcoded date

	date = datetime.datetime.strptime(date, '%Y-%m-%d') #setting the date format
	date = date.date() #given hardcoded date
	week_ago = date - datetime.timedelta(days=7)  #what will be the data week_ago
	twoweeks_ago = week_ago - datetime.timedelta(days=7)  #what will be the data two_week_ago
	data = JaGAnalyticsData.objects.filter(date__range=[week_ago,date])  #what will be the data? in JaGAnalyticsData for week_ago
	#setting dates for 1 week
	previous_data = JaGAnalyticsData.objects.filter(date__range=[twoweeks_ago,week_ago]) #what will be the previous data? in JaGAnalyticsData for two_week_ago
	#revenue
	revenue = data.aggregate(total=Sum('total_revenue'))  #total revenue(last week)
	revenue1 = previous_data.aggregate(total=Sum('total_revenue')) #total revenue (current week)
	diff  = (revenue['total']-revenue1['total'])/revenue['total']
	diff = diff*100  #display Arrow up of down?

	#conversions
	conversions =  data.aggregate(total=Sum('conversion_rate'))
	conversions1 =  previous_data.aggregate(total=Sum('conversion_rate'))
	diffc  = (conversions['total']-conversions1['total'])/conversions['total']
	diffc = diffc*100  #display Arrow up of down?

	#transactions
	transactions = data.aggregate(total=Sum('total_transactions'))
	transactions1 = previous_data.aggregate(total=Sum('total_transactions'))
	difft  = (transactions['total']-transactions1['total'])/transactions['total']
	difft = difft*100  #display Arrow up of down?

	#avg order value
	avg = data.aggregate(total=Sum('avg_order_value'))
	avg1 = previous_data.aggregate(total=Sum('avg_order_value'))
	diffa  = (avg['total']-avg1['total'])/avg['total']
	diffa = diffa*100  #display Arrow up of down?


	adsclick = data.aggregate(total=Sum('total_ads_clicks'))
	adsclick1 = previous_data.aggregate(total=Sum('total_ads_clicks'))
	adscost = data.aggregate(total=Sum('total_ads_cost'))
	adscost1 = previous_data.aggregate(total=Sum('total_ads_cost'))
	adscpc = data.aggregate(total=Sum('ads_cpc_value'))
	adscpc1 = previous_data.aggregate(total=Sum('ads_cpc_value'))
	nvis = data.aggregate(total=Sum('new_visitors'))
	nvis1 = previous_data.aggregate(total=Sum('new_visitors'))
	rvis = data.aggregate(total=Sum('returning_visitors'))
	rvis1 = previous_data.aggregate(total=Sum('returning_visitors'))
	tuser = data.aggregate(total=Sum('total_users'))
	tuser1 = previous_data.aggregate(total=Sum('total_users'))
	tpviews = data.aggregate(total=Sum('total_pageviews'))
	tpviews1 = previous_data.aggregate(total=Sum('total_pageviews'))
	brate = data.aggregate(total=Sum('bounce_rate'))
	brate1 = previous_data.aggregate(total=Sum('bounce_rate'))
	sesdur = data.aggregate(total=Sum('session_duration'))
	sesdur1 = previous_data.aggregate(total=Sum('session_duration'))
	ouser = data.aggregate(total=Sum('total_organic_users'))
	ouser1 = previous_data.aggregate(total=Sum('total_organic_users'))
	duser = data.aggregate(total=Sum('total_direct_users'))
	duser1 = previous_data.aggregate(total=Sum('total_direct_users'))
	puser = data.aggregate(total=Sum('total_paid_users'))
	puser1 = previous_data.aggregate(total=Sum('total_paid_users'))
	fclick = data.aggregate(total=Sum('social_facebook_clicks'))
	fclick1 = previous_data.aggregate(total=Sum('social_facebook_clicks'))
	insclick = data.aggregate(total=Sum('social_instagram_clicks'))
	insclick1 = previous_data.aggregate(total=Sum('social_instagram_clicks'))
	pinclick = data.aggregate(total=Sum('social_pinterest_clicks'))
	pinclick1 = previous_data.aggregate(total=Sum('social_pinterest_clicks'))
	twclick = data.aggregate(total=Sum('social_twitter_clicks'))
	twclick1 = previous_data.aggregate(total=Sum('social_twitter_clicks'))


	mcfdir = JaConversionValue.objects.filter(date__range=[week_ago,date]).filter(cs_id=1)
	mcfdir1 = mcfdir.aggregate(total=Sum('total_conversion'))
	mcfdir2= mcfdir.aggregate(total=Sum('conversion_value'))

	mcfp = JaConversionValue.objects.filter(date__range=[week_ago,date]).filter(cs_id=2)
	mcfp1 = mcfp.aggregate(total=Sum('total_conversion'))
	mcfp2= mcfp.aggregate(total=Sum('conversion_value'))

	mcf = JaConversionValue.objects.filter(date__range=[week_ago,date]).filter(cs_id=3)
	mcf1 = JaConversionValue.objects.filter(date__range=[twoweeks_ago,week_ago]).filter(cs_id=3)
	mcfvalue = mcf.aggregate(total=Sum('total_conversion'))
	mcfvalue1= mcf.aggregate(total=Sum('conversion_value'))

	mcfref = JaConversionValue.objects.filter(date__range=[week_ago,date]).filter(cs_id=4)
	mcfref1 = mcfref.aggregate(total=Sum('total_conversion'))
	mcfref2= mcfref.aggregate(total=Sum('conversion_value'))

	mcfemail = JaConversionValue.objects.filter(date__range=[week_ago,date]).filter(cs_id=5)
	mcfemail1 = mcfemail.aggregate(total=Sum('total_conversion'))
	mcfemail2= mcfemail.aggregate(total=Sum('conversion_value'))


	#revenue = (JaGAnalyticsData.objects.filter(date__gt=week_ago).extra(select={'day': 'date(date)'}).values('day').annotate(sum=Sum('total_revenue')))
	analytics = JaGAnalyticsData.objects.all()
	return render(request, 'home/index.html', {'twclick':twclick,'twclick1':twclick1,'pinclick':pinclick,'pinclick1':pinclick1,'insclick1':insclick1,'insclick':insclick,'fclick':fclick,'fclick1':fclick1,'duser1':duser1,'duser':duser,'puser1':puser1,'puser':puser,'ouser1':ouser1,'ouser':ouser,'sesdur1':sesdur1,'sesdur':sesdur,'brate':brate,'brate1':brate1,'tpviews':tpviews, 'tpviews1':tpviews1, 'nvis':nvis,'nvis1':nvis1,'rvis':rvis,'rvis1':rvis1,'tuser':tuser,'tuser1':tuser1,'adsclick':adsclick,'adsclick1':adsclick1,'adscost':adscost,'adscost1':adscost1, 'adscpc':adscpc,'adscpc1':adscpc1, 'mcfp2':mcfp2,'mcfp1':mcfp1,'mcfdir2':mcfdir2,'mcfdir1':mcfdir1,'mcfemail1':mcfemail1,'mcfemail2':mcfemail2,'mcfref2':mcfref2,'mcfref1':mcfref1,'mcfvalue1':mcfvalue1,'mcfvalue':mcfvalue, 'avg':avg, 'avg1':avg1, 'transactions1':transactions1, 'conversions1':conversions1, 'diff':diff,'diffc':diffc,'difft':difft,'diffa':diffa, 'analytics':analytics, 'revenue':revenue, 'conversions':conversions, 'transactions':transactions, 'revenue1':revenue1})
   
   

@login_required(login_url="/login/")
def pages(request):
    context = {}
    # All resource paths end in .html.
    # Pick out the html file name from the url. And load that template.
    try:

        load_template = request.path.split('/')[-1]

        if load_template == 'admin':
            return HttpResponseRedirect(reverse('admin:index'))
        context['segment'] = load_template

        html_template = loader.get_template('home/' + load_template)
        return HttpResponse(html_template.render(context, request))

    except template.TemplateDoesNotExist:

        html_template = loader.get_template('home/page-404.html')
        return HttpResponse(html_template.render(context, request))

    except:
        html_template = loader.get_template('home/page-500.html')
        return HttpResponse(html_template.render(context, request))
