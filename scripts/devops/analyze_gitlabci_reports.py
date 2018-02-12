#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import requests
from lxml import etree
import os
import sys
import threading
import queue

if 'GITLAB_TOKEN' in os.environ:
    token = os.environ['GITLAB_TOKEN']
else:
    print('Get a personal access token from https://gitlab.com/profile/personal_access_tokens and then:')
    print('GITLAB_TOKEN=<your token here> ' + ' '.join(sys.argv))
    sys.exit(1)

number = sys.argv[1] if len(sys.argv) > 1 else 5
jobs_url = f"https://gitlab.com/api/v4/projects/foodsharing-dev%2Ffoodsharing/jobs?scope[]=failed&scope[]=success&per_page={number}"
report_url = "https://gitlab.com/api/v4/projects/foodsharing-dev%2Ffoodsharing/jobs/{job}/artifacts/tests/_output/_output/report.xml"

def get_jobs():
    return requests.get(jobs_url, headers={'PRIVATE-TOKEN': token}).json()

def get_report(job_id):
    r = requests.get(report_url.format(job=job_id), headers={'PRIVATE-TOKEN': token})
    if (r.status_code == 200):
        return etree.fromstring(r.content)
    return None

def analyze_report(report):
    output = {}
    for suite in report:
        for case in suite:
            identity = case.get('name')
            feature = case.get('feature')
            if feature:
                identity += ' ' + feature
            output[identity] = {'result': 'success', 'time': case.get('time')}

            if len(case) > 0:
                output[identity].update({'result': case[0].tag, 'result_payload': dict(case[0].items())})
    return output

def worker():
    while True:
        job_id = worker_queue.get()
        if job_id is None:
            break
        print(f'Downloading report for job {job_id}...')
        report = get_report(job_id)
        if report is not None:
            analyzed_reports.append(analyze_report(report))
        worker_queue.task_done()
    

analyzed_reports = []
worker_queue = queue.Queue()
threads = [threading.Thread(target=worker) for _ in range(5)]
[t.start() for t in threads]

print(f'Getting {number} jobs...')
[worker_queue.put(job['id']) for job in get_jobs()]
worker_queue.join()
[worker_queue.put(None) for _ in range(len(threads))]
[t.join() for t in threads]


#%%
test_overview = {}
for report in analyzed_reports:
    for test, values in report.items():
        if test not in test_overview:
            test_overview[test] = []
        test_overview[test].append(values)

#%%
test_failure_summary = {}
for test, values in test_overview.items():
    test_failure_summary[test] = sum([1 for v in values if v['result'] != 'success'])

highest_failures = sorted(test_failure_summary.items(), key=lambda x: x[1], reverse=True)
print()
print('Tests with highest failure count:')
for t in highest_failures:
    if t[1] > 0:
        print(t[1], t[0])






