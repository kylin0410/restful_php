import requests
import json

import config


def login_admin():
    api = config.base_url + '/api/auth/login'
    resp = requests.post(api, data=json.dumps(config.admin_auth_data))
    json_obj = json.loads(resp.text)
    config.header = dict()
    config.header["Authorization"] = json_obj['data']['token']


def logout_admin():
    config.header = dict()
