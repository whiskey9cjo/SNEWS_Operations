Build container:
module load apptainer
apptainer build grafana.sif docker://grafana/grafana-oss

Run container:
module load apptainer
apptainer run --env-file grafana.env grafana.sif
