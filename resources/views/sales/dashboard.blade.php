<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .card h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .card p {
            color: #666;
            line-height: 1.6;
        }
        
        .workflow {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .workflow h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .step {
            flex: 1;
            min-width: 120px;
            text-align: center;
            padding: 15px;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            font-size: 20px;
        }
        
        .step-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .step-desc {
            font-size: 12px;
            color: #666;
        }
        
        .arrow {
            font-size: 30px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💼 Sales Management System</h1>
            <p>Complete sales tracking with commission and payment management</p>
        </div>
        
        <div class="cards">
            <div class="card">
                <div class="card-icon">🛒</div>
                <h3>Sales Management</h3>
                <p>Create and manage sales orders. Track customer purchases, assign agents, and monitor order status from pending to completion.</p>
            </div>
            
            <div class="card">
                <div class="card-icon">💰</div>
                <h3>Payment Tracking</h3>
                <p>Record deposits and balance payments. Support multiple payment methods including cash, bank transfer, QR codes, and credit cards.</p>
            </div>
            
            <div class="card">
                <div class="card-icon">💵</div>
                <h3>Commission System</h3>
                <p>Automatic 5% commission calculation for sales agents. Track pending and paid commissions with full payment history.</p>
            </div>
            
            <div class="card">
                <div class="card-icon">📦</div>
                <h3>Stock Management</h3>
                <p>Automated stock reduction when orders are processed. Complete stock movement tracking with references to sales.</p>
            </div>
            
            <div class="card">
                <div class="card-icon">📊</div>
                <h3>Reports & Analytics</h3>
                <p>Agent performance reports, commission tracking, payment analysis, and detailed financial summaries.</p>
            </div>
            
            <div class="card">
                <div class="card-icon">🔔</div>
                <h3>Notifications</h3>
                <p>Status updates for customers and agents. Payment reminders and order ready notifications.</p>
            </div>
        </div>
        
        <div class="workflow">
            <h2>📈 Sales Workflow</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-name">PENDING</div>
                    <div class="step-desc">Sale created</div>
                </div>
                <div class="arrow">→</div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-name">DEPOSITED</div>
                    <div class="step-desc">Deposit received</div>
                </div>
                <div class="arrow">→</div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-name">PROCESSING</div>
                    <div class="step-desc">Stock reduced</div>
                </div>
                <div class="arrow">→</div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-name">READY</div>
                    <div class="step-desc">Items ready</div>
                </div>
                <div class="arrow">→</div>
                <div class="step">
                    <div class="step-number">5</div>
                    <div class="step-name">COMPLETED</div>
                    <div class="step-desc">Commission paid</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
