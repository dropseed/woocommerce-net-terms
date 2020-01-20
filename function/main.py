import json
import os
import requests
import sys


# @client.capture_exceptions
def gcp_handler(request):
    import flask

    if request.method != "GET":
        flask.abort(405)

    auth = request.headers.get("Authorization", "")
    try:
        license_key = auth.split()[1]
    except KeyError:
        flask.abort(401)

    data, status_code = handle(license_key)

    if status_code >= 400:
        flask.abort(status_code)

    return json.dumps(data)


def handle(license_key):
    if not license_key:
        return {}, 401

    if not license_key_is_valid(license_key):
        return {}, 403

    status_code = 200

    with open(os.path.join(os.path.dirname(__file__), "data.json"), "r") as f:
        data = json.load(f)

    # or, if not valid then just don't include download link? disable updating...

    return data, status_code


def license_key_is_valid(key):
    print(f"validating_license_key key={key}")
    response = requests.post("https://api.gumroad.com/v2/licenses/verify", params={
        "product_permalink": os.environ["GUMROAD_PRODUCT_PERMALINK"],
        "license_key": key,
    })
    print(response)
    print(response.text)
    return response.status_code < 400


if __name__ == "__main__":
    data, status_code = handle(sys.argv[1])
    print(f"Status: {status_code}")
    print(json.dumps(data, indent=2))
