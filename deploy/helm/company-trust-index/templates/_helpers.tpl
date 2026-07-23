{{- define "company-trust-index.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{- define "company-trust-index.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{- define "company-trust-index.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{- define "company-trust-index.labels" -}}
helm.sh/chart: {{ include "company-trust-index.chart" . }}
{{ include "company-trust-index.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{- define "company-trust-index.selectorLabels" -}}
app.kubernetes.io/name: {{ include "company-trust-index.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{- define "company-trust-index.secretName" -}}
{{- if .Values.existingSecret }}
{{- .Values.existingSecret }}
{{- else if .Values.secret.name }}
{{- .Values.secret.name }}
{{- else }}
{{- include "company-trust-index.fullname" . }}
{{- end }}
{{- end }}

{{- define "company-trust-index.mariadbName" -}}
{{- printf "%s-mariadb" (include "company-trust-index.fullname" .) | trunc 63 | trimSuffix "-" }}
{{- end }}

{{- define "company-trust-index.mariadbPasswordSecretName" -}}
{{- printf "%s-mariadb-password" (include "company-trust-index.fullname" .) | trunc 63 | trimSuffix "-" }}
{{- end }}

{{- define "company-trust-index.mariadbHost" -}}
{{- include "company-trust-index.mariadbName" . }}
{{- end }}
