from flask import Flask, request, jsonify
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys

import time
from datetime import datetime

app = Flask(__name__)

@app.route('/application_status', methods=['POST'])
def application_status():
    data = request.get_json()
    file_no = data.get('file_no')
    dob = data.get('dob')

    if not file_no or not dob:
        return jsonify({'error': 'file_no and dob are required'}), 400

    try:
        # Setup headless Chrome
        chrome_options = Options()
        chrome_options.add_argument("--headless")
        chrome_options.add_argument("--no-sandbox")
        chrome_options.add_argument("--disable-dev-shm-usage")
        driver = webdriver.Chrome(options=chrome_options)

        driver.get("https://www.passportindia.gov.in/psp/trackApplicationService")
        time.sleep(3)

        # Wait for dropdown and select "Application Status"
        select_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.XPATH, '//*[@id="root"]/div/div[6]/div/div[1]/div/form/div[1]/div/select'))
        )
        Select(select_element).select_by_index(1)
        time.sleep(1)

        # Enter file number
        file_input = driver.find_element(By.ID, "formBasicEmail")
        file_input.clear()
        file_input.send_keys(file_no)

        # Wait for the element to be present
        dob_input = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.XPATH, '//*[@id="root"]/div/div[6]/div/div[1]/div/form/div[2]/div[2]/div/input'))
        )
        dob_input.clear()
        dob_input.send_keys(dob)
        dob_input.send_keys(Keys.TAB)
        time.sleep(0.5)

        # Click submit
        submit_btn_xpath = '//*[@id="root"]/div/div[6]/div/div[1]/div/form/div[2]/div[4]/div[1]/button'
        submit_btn = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable((By.XPATH, submit_btn_xpath))
        )
        driver.execute_script("arguments[0].scrollIntoView(true);", submit_btn)
        time.sleep(0.5)
        driver.execute_script("arguments[0].click();", submit_btn)

        time.sleep(1)
        # Wait for the application status to load
        status_xpath = '//*[@id="root"]/div/div[6]/div/div[2]/div/div/div[1]/div/table/tbody/tr[7]/td[2]/span/span'
        status_element = WebDriverWait(driver, 15).until(
            EC.visibility_of_element_located((By.XPATH, status_xpath))
        )
        status_text = status_element.text.strip()

         # Save debug HTML after submission
        # save_debug_page(driver, 'debug_page.html')

        driver.quit()

        return jsonify({
            'status': "ok",
            'file_no': file_no,
            'dob': dob,
            'message': status_text,
        }), 200
    except Exception as e:
        if 'driver' in locals():
            # Save debug HTML after submission
            # save_debug_page(driver+str(e), 'submitted_page.html')
            driver.quit()
        return jsonify({'error': str(e)}), 500

def save_debug_page(driver, filename='debug_page.html'):
    with open(filename, 'w', encoding='utf-8') as f:
        f.write(driver.page_source)

if __name__ == '__main__':
    app.run(debug=True)