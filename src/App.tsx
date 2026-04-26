import { useState } from 'react'

export default function App() {
  const [copied, setCopied] = useState<string | null>(null)

  const copyText = (text: string, label: string) => {
    navigator.clipboard.writeText(text)
    setCopied(label)
    setTimeout(() => setCopied(null), 2000)
  }

  const credentials = [
    { role: 'Admin', name: 'Eshan', email: 'admin@pharma.bd', password: '12345!', icon: '👑', color: '#dc3545' },
    { role: 'Customer', name: 'Milton', email: 'milton@pharma.bd', password: '12345', icon: '🛒', color: '#198754' },
    { role: 'Salesperson', name: 'Tanjim', email: 'tanjim@pharma.bd', password: '12345', icon: '🏪', color: '#ffc107' },
  ]

  const features = [
    { icon: '💊', title: 'Medicine Shop', desc: 'Browse & buy medicines by category with search & filters' },
    { icon: '🛒', title: 'Smart Cart', desc: 'Add to cart, manage quantities, apply delivery costs' },
    { icon: '📦', title: 'Order Tracking', desc: 'Track orders from pending to delivery in real-time' },
    { icon: '⭐', title: 'Verified Reviews', desc: 'Only customers who bought can review (per order)' },
    { icon: '📷', title: 'Medicine Photos', desc: 'Salesperson can upload actual medicine images' },
    { icon: '🚚', title: 'BD Delivery', desc: 'All 8 divisions, 64 districts delivery coverage' },
    { icon: '💳', title: 'MFS Payment', desc: 'bKash, Nagad, Rocket & Cash on Delivery' },
    { icon: '👤', title: 'Profile Editor', desc: 'Edit profile pic, info & password from any page' },
    { icon: '💬', title: 'Feedback', desc: 'Customer feedback & contact system' },
    { icon: '👥', title: 'Admin Panel', desc: 'Full user management, order tracking, reports' },
    { icon: '🔐', title: 'Role Based Access', desc: 'Admin, Salesperson, Customer — each has own dashboard' },
    { icon: '🚪', title: 'Logout Everywhere', desc: 'Logout button visible from every page in navbar' },
  ]

  return (
    <div style={{ fontFamily: "'Inter', sans-serif", minHeight: '100vh', background: '#f0f2f5' }}>
      { }
      <div style={{
        background: 'linear-gradient(135deg, #0d6efd 0%, #0a58ca 60%, #084298 100%)',
        color: 'white',
        padding: '60px 20px',
        textAlign: 'center',
      }}>
        <div style={{ fontSize: '4rem', marginBottom: '16px' }}>💊</div>
        <h1 style={{ fontSize: '2.8rem', fontWeight: 800, marginBottom: '12px' }}>
          Pharmacy Management System
        </h1>
        <p style={{ fontSize: '1.2rem', opacity: 0.9, marginBottom: '8px' }}>
          Bangladesh's Full-Stack PHP Pharmacy Management System
        </p>
        <p style={{ fontSize: '0.95rem', opacity: 0.75, marginBottom: '32px' }}>
          Built with PHP, MySQL (XAMPP), HTML, CSS & JavaScript
        </p>

        <div style={{
          display: 'inline-flex',
          background: 'rgba(255,215,0,0.2)',
          border: '2px solid rgba(255,215,0,0.6)',
          borderRadius: '12px',
          padding: '16px 28px',
          marginBottom: '32px',
          gap: '16px',
          alignItems: 'center',
          flexWrap: 'wrap',
          justifyContent: 'center',
        }}>
          <span style={{ fontSize: '1.5rem' }}>🚀</span>
          <div style={{ textAlign: 'left' }}>
            <div style={{ fontWeight: 700, fontSize: '1rem' }}>To run this system:</div>
            <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>Open XAMPP → Start Apache & MySQL → Navigate to:</div>
            <code style={{
              background: 'rgba(0,0,0,0.3)',
              padding: '4px 10px',
              borderRadius: '6px',
              fontSize: '0.9rem',
              display: 'block',
              marginTop: '4px',
            }}>
              http://localhost/pharmacy/index.php
            </code>
          </div>
        </div>

        <div>
          <a
            href="/pharmacy/index.php"
            style={{
              background: '#ffd700',
              color: '#000',
              padding: '14px 36px',
              borderRadius: '10px',
              textDecoration: 'none',
              fontWeight: 700,
              fontSize: '1.1rem',
              display: 'inline-flex',
              alignItems: 'center',
              gap: '8px',
            }}
          >
            🏥 Open Pharmacy System
          </a>
        </div>
      </div>

      <div style={{ maxWidth: '1200px', margin: '0 auto', padding: '40px 20px' }}>

        <div style={{ marginBottom: '40px' }}>
          <h2 style={{ fontSize: '1.5rem', fontWeight: 700, marginBottom: '20px', textAlign: 'center' }}>
            🔑 Login Credentials
          </h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '16px' }}>
            {credentials.map((cred) => (
              <div key={cred.role} style={{
                background: 'white',
                borderRadius: '16px',
                padding: '24px',
                boxShadow: '0 4px 20px rgba(0,0,0,0.08)',
                borderTop: `4px solid ${cred.color}`,
              }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '16px' }}>
                  <span style={{ fontSize: '2rem' }}>{cred.icon}</span>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: '1.1rem' }}>{cred.role}</div>
                    <div style={{ color: '#666', fontSize: '0.9rem' }}>{cred.name}</div>
                  </div>
                  <span style={{
                    marginLeft: 'auto',
                    background: cred.color + '20',
                    color: cred.color,
                    padding: '4px 10px',
                    borderRadius: '20px',
                    fontSize: '0.75rem',
                    fontWeight: 600,
                  }}>{cred.role}</span>
                </div>

                {[
                  { label: 'Email', value: cred.email },
                  { label: 'Password', value: cred.password },
                ].map(({ label, value }) => (
                  <div key={label} style={{
                    background: '#f8f9fa',
                    borderRadius: '8px',
                    padding: '10px 14px',
                    marginBottom: '8px',
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                  }}>
                    <div>
                      <div style={{ fontSize: '0.75rem', color: '#888', marginBottom: '2px' }}>{label}</div>
                      <code style={{ fontWeight: 600, fontSize: '0.9rem' }}>{value}</code>
                    </div>
                    <button
                      onClick={() => copyText(value, `${cred.role}-${label}`)}
                      style={{
                        background: copied === `${cred.role}-${label}` ? '#198754' : '#e9ecef',
                        border: 'none',
                        borderRadius: '6px',
                        padding: '6px 12px',
                        cursor: 'pointer',
                        fontSize: '0.8rem',
                        color: copied === `${cred.role}-${label}` ? 'white' : '#333',
                        transition: 'all 0.2s',
                      }}
                    >
                      {copied === `${cred.role}-${label}` ? '✅ Copied' : '📋 Copy'}
                    </button>
                  </div>
                ))}
              </div>
            ))}
          </div>
        </div>

        <div style={{ marginBottom: '40px' }}>
          <h2 style={{ fontSize: '1.5rem', fontWeight: 700, marginBottom: '20px', textAlign: 'center' }}>
            ✨ System Features
          </h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(240px, 1fr))', gap: '16px' }}>
            {features.map((f) => (
              <div key={f.title} style={{
                background: 'white',
                borderRadius: '12px',
                padding: '20px',
                boxShadow: '0 4px 20px rgba(0,0,0,0.06)',
                display: 'flex',
                gap: '12px',
                alignItems: 'flex-start',
              }}>
                <span style={{ fontSize: '1.8rem', flexShrink: 0 }}>{f.icon}</span>
                <div>
                  <div style={{ fontWeight: 600, marginBottom: '4px' }}>{f.title}</div>
                  <div style={{ fontSize: '0.82rem', color: '#666', lineHeight: 1.5 }}>{f.desc}</div>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div style={{
          background: 'white',
          borderRadius: '16px',
          padding: '32px',
          boxShadow: '0 4px 20px rgba(0,0,0,0.08)',
        }}>
          <h2 style={{ fontSize: '1.3rem', fontWeight: 700, marginBottom: '20px' }}>🛠️ Setup Guide</h2>
          {[
            { step: 1, text: 'Install XAMPP and start Apache + MySQL services' },
            { step: 2, text: 'Copy the entire "pharmacy" folder to C:/xampp/htdocs/' },
            { step: 3, text: 'Open http:
            { step: 4, text: 'Database "pharmacy_bd" will be auto-created on first visit' },
            { step: 5, text: 'Login with the credentials above to explore each role' },
          ].map(({ step, text }) => (
            <div key={step} style={{
              display: 'flex',
              alignItems: 'center',
              gap: '16px',
              padding: '14px 0',
              borderBottom: step < 5 ? '1px solid #f0f0f0' : 'none',
            }}>
              <div style={{
                width: '32px', height: '32px',
                background: '#0d6efd',
                color: 'white',
                borderRadius: '50%',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontWeight: 700,
                flexShrink: 0,
              }}>{step}</div>
              <span style={{ color: '#444' }}>{text}</span>
            </div>
          ))}
        </div>

        { }
        <div style={{
          marginTop: '24px',
          background: '#1a1f2e',
          borderRadius: '16px',
          padding: '24px 32px',
          color: '#a8b2d8',
        }}>
          <h3 style={{ color: 'white', marginBottom: '16px', fontSize: '1rem' }}>⚙️ Tech Stack</h3>
          <div style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
            {['PHP 8+', 'MySQL (XAMPP)', 'HTML5', 'CSS3', 'JavaScript ES6', 'Google Fonts', 'Session Auth', 'File Upload API'].map(tech => (
              <span key={tech} style={{
                background: 'rgba(255,255,255,0.1)',
                padding: '6px 14px',
                borderRadius: '20px',
                fontSize: '0.82rem',
                color: '#cdd6f4',
              }}>{tech}</span>
            ))}
          </div>
        </div>

        <div style={{ textAlign: 'center', marginTop: '32px', color: '#888', fontSize: '0.85rem' }}>
          © 2025 Pharmacy Management System — Made with ❤️ for Bangladesh 🇧🇩
        </div>
      </div>
    </div>
  )
}

