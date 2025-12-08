import React, { useState } from 'react';
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import styles from './ApiPlayground.module.css';

interface ApiPlaygroundProps {
    method: 'GET' | 'POST' | 'PUT' | 'DELETE';
    endpoint: string;
    body?: Record<string, any>;
    params?: Record<string, string>;
    queryParams?: Record<string, string>;
    headers?: Record<string, string>;
    description?: string;
}

const ApiPlayground: React.FC<ApiPlaygroundProps> = ({
    method,
    endpoint,
    body = {},
    params = {},
    queryParams = {},
    headers = {},
    description,
}) => {
    const { siteConfig } = useDocusaurusContext();
    const [response, setResponse] = useState<any>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [editableBody, setEditableBody] = useState(JSON.stringify(body, null, 2));
    const [editableParams, setEditableParams] = useState(params);
    const [editableQuery, setEditableQuery] = useState(queryParams);
    const [editableHeaders, setEditableHeaders] = useState(headers);

    const configuredApiUrl = (siteConfig.customFields as { apiUrl?: string })?.apiUrl;
    const apiUrl = typeof window !== 'undefined'
        ? localStorage.getItem('API_URL')
            || configuredApiUrl
            || (window as any).ENV?.API_URL
            || 'http://localhost:8000/api'
        : configuredApiUrl || 'http://localhost:8000/api';

    const buildUrl = () => {
        let url = endpoint;

        // Replace path params
        Object.entries(editableParams).forEach(([key, value]) => {
            if (value && value.trim() !== '') {
                url = url.replace(`{${key}}`, value);
            } else {
                // If param is empty, show a placeholder
                url = url.replace(`{${key}}`, `{${key}}`);
            }
        });

        // Add query params
        const queryString = Object.entries(editableQuery)
            .filter(([_, value]) => value && value !== '')
            .map(([key, value]) => `${key}=${encodeURIComponent(value)}`)
            .join('&');

        return `${apiUrl}${url}${queryString ? '?' + queryString : ''}`;
    };

    const handleExecute = async () => {
        // Validate required path params
        const missingParams = Object.entries(editableParams).filter(([key, value]) => 
            !value || value.trim() === ''
        );

        if (missingParams.length > 0) {
            setError(`Missing required path parameters: ${missingParams.map(([key]) => key).join(', ')}`);
            return;
        }

        setLoading(true);
        setError(null);
        setResponse(null);

        try {
            const requestHeaders: Record<string, string> = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...editableHeaders,
            };

            const options: RequestInit = {
                method,
                headers: requestHeaders,
            };

            if (method !== 'GET' && editableBody.trim()) {
                options.body = editableBody;
            }

            const res = await fetch(buildUrl(), options);
            const data = await res.json();

            setResponse({
                status: res.status,
                statusText: res.statusText,
                data,
            });
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Request failed');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className={styles.playground}>
            {description && <p className={styles.description}>{description}</p>}

            <div className={styles.request}>
                <div className={styles.header}>
                    <span className={`${styles.method} ${styles[method.toLowerCase()]}`}>
                        {method}
                    </span>
                    <code className={styles.endpoint}>{buildUrl()}</code>
                </div>

                {/* Custom Headers */}
                {Object.keys(headers).length > 0 && (
                    <div className={styles.section}>
                        <h4>Headers</h4>
                        {Object.entries(editableHeaders).map(([key, value]) => (
                            <div key={key} className={styles.param}>
                                <label>{key}:</label>
                                {key === 'X-LOCATION-ADAPTER' || key === 'X-TAXONOMY-ADAPTER' ? (
                                    <select
                                        value={value}
                                        onChange={(e) => setEditableHeaders({ ...editableHeaders, [key]: e.target.value })}
                                        className={styles.adapterSelect}
                                    >
                                        <option value="">SQL</option>
                                        <option value="local">Local</option>
                                    </select>
                                ) : (
                                    <input
                                        type="text"
                                        value={value}
                                        onChange={(e) => setEditableHeaders({ ...editableHeaders, [key]: e.target.value })}
                                        placeholder={`Enter ${key}`}
                                    />
                                )}
                            </div>
                        ))}
                    </div>
                )}

                {/* Path Parameters */}
                {Object.keys(params).length > 0 && (
                    <div className={styles.section}>
                        <h4>Path Parameters</h4>
                        {Object.entries(editableParams).map(([key, value]) => (
                            <div key={key} className={styles.param}>
                                <label>{key}:</label>
                                <input
                                    type="text"
                                    value={value}
                                    onChange={(e) => setEditableParams({ ...editableParams, [key]: e.target.value })}
                                    placeholder={`Enter ${key}`}
                                />
                            </div>
                        ))}
                    </div>
                )}

                {/* Query Parameters */}
                {Object.keys(queryParams).length > 0 && (
                    <div className={styles.section}>
                        <h4>Query Parameters</h4>
                        {Object.entries(editableQuery).map(([key, value]) => (
                            <div key={key} className={styles.param}>
                                <label>{key}:</label>
                                <input
                                    type="text"
                                    value={value}
                                    onChange={(e) => setEditableQuery({ ...editableQuery, [key]: e.target.value })}
                                    placeholder={`Enter ${key} (optional)`}
                                />
                            </div>
                        ))}
                    </div>
                )}

                {/* Request Body */}
                {method !== 'GET' && Object.keys(body).length > 0 && (
                    <div className={styles.section}>
                        <h4>Request Body</h4>
                        <textarea
                            className={styles.bodyEditor}
                            value={editableBody}
                            onChange={(e) => setEditableBody(e.target.value)}
                            rows={8}
                        />
                    </div>
                )}

                <button
                    className={styles.executeBtn}
                    onClick={handleExecute}
                    disabled={loading}
                >
                    {loading ? '⏳ Executing...' : '▶ Execute Request'}
                </button>
            </div>

            {/* Response */}
            {response && (
                <div className={styles.response}>
                    <h4>Response</h4>
                    <div className={styles.statusBar}>
                        <span className={`${styles.status} ${response.status < 300 ? styles.success : styles.error}`}>
                            {response.status} {response.statusText}
                        </span>
                    </div>
                    <pre className={styles.responseBody}>
                        {JSON.stringify(response.data, null, 2)}
                    </pre>
                </div>
            )}

            {error && (
                <div className={styles.error}>
                    <strong>Error:</strong> {error}
                </div>
            )}
        </div>
    );
};

export default ApiPlayground;
