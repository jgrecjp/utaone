FROM python:3.13-slim

ENV PYTHONDONTWRITEBYTECODE=1 PYTHONUNBUFFERED=1 PYTHONPATH=/app/services/worker
WORKDIR /app
RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg libsndfile1 && rm -rf /var/lib/apt/lists/*
COPY services/worker/requirements.txt /tmp/worker-requirements.txt
RUN pip install --no-cache-dir -r /tmp/worker-requirements.txt
COPY services/worker /app/services/worker
CMD ["python", "-m", "utaone_worker"]
