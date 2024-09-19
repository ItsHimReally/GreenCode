import uvicorn


host="0.0.0.0"
port=8999
app_name="app.api:app"


if __name__ == "__main__":
    uvicorn.run(app_name, host=host, port=port, reload=True)
